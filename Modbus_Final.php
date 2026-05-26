<?php
//session_start();
ini_set("error_reporting", E_ALL);
set_time_limit(0);
//require_once 'server.php';
$db=mysqli_connect('localhost','root','Password@123','moresseh_daas');
$select_qu="select * from keystore";
$query_se=mysqli_query($db,$select_qu);
$date_se=mysqli_fetch_assoc($query_se);
 $md_string="mack".$date_se['end_date'];
$encystring=md5($md_string);

if($encystring!=$date_se['check'])
{
	echo 'Hi';
 exit(); // Exit if the lock is already held by another process
}
$lockFile = "C:\\Windows\\Temp\\modbus_final.lock";

// Open the lock file
$fp = fopen($lockFile, "w+");

if (!$fp) {
    die("Unable to open the lock file. Check file permissions.\n");
}

// Check if we can acquire the lock (non-blocking)
if (!flock($fp, LOCK_EX | LOCK_NB)) {
    echo "Another process is already running.\n";
    sleep(5);
}

use ModbusTcpClient\Network\BinaryStreamConnection;
use ModbusTcpClient\Packet\ModbusFunction\ReadCoilsRequest;
use ModbusTcpClient\Packet\ModbusFunction\ReadCoilsResponse;
use ModbusTcpClient\Packet\ModbusFunction\ReadHoldingRegistersRequest;
use ModbusTcpClient\Packet\ModbusFunction\ReadHoldingRegistersResponse;
use ModbusTcpClient\Packet\ModbusFunction\WriteSingleRegisterRequest;
use ModbusTcpClient\Packet\ModbusFunction\WriteSingleRegisterResponse;
use ModbusTcpClient\Packet\ResponseFactory;
use ModbusTcpClient\Utils\Endian;
while (TRUE) {
    require_once 'server.php';

	
	$db=mysqli_connect('localhost','root','Password@123','moresseh_daas');
    $sql = "SELECT * FROM all_modbus_run WHERE id='1'";
    $query = mysqli_query($db, $sql);
    $fetch = mysqli_fetch_assoc($query);
    $select_ipN="select ip_address from  chamber_master where active_flg=1 ";
	$ip_queryN=mysqli_query($db,$select_ipN);
	while($ser_rowN=mysqli_fetch_assoc($ip_queryN))
	{
		$ser_rowIP=$ser_rowN['ip_address'];
        if ($fetch['modbus_run_status'] == 2) {
			Modbus_Final($ser_rowIP);
        } elseif ($fetch['modbus_run_status'] == 1) {
			Modbus_Event($ser_rowIP);
        } /*elseif ($fetch['modbus_run_status'] == 2) {
          SetDateTime();
        } elseif ($fetch['modbus_run_status'] == 3) {
            
        } elseif ($fetch['modbus_run_status'] == 4) {
           
        }*/
	}

    // Wait for 10 seconds
   sleep(2);

}
// Release the lock and close the file when the loop finishes (if it ever does)
//flock($fp, LOCK_UN);
//fclose($fp);

function Modbus_Final($ser_rowIP)
{
$tranpip=$ser_rowIP;
ini_set("error_reporting", E_ALL);
/* Remove the execution time limit */
set_time_limit(0);

/* Iteration interval in seconds */
$sleep_time = 60;
require __DIR__ . '/vendor/autoload.php';

// Report all errors except E_NOTICE
error_reporting(E_ALL & ~E_NOTICE);

require_once 'phpmailer/PHPMailerAutoload.php';

$dbm=mysqli_connect('localhost','root','Password@123','moresseh_daas');
$selectemail="select * from email_server_setting";
$query_email=mysqli_query($dbm,$selectemail);
$row_email=mysqli_fetch_assoc($query_email);

$SelectTime="select * from timezone";
$querytime=mysqli_query($dbm,$SelectTime);
$rowTime=mysqli_fetch_assoc($querytime);

date_default_timezone_set($rowTime['timezone']); 
$TimeChange=date("H:i");
$port = 502;
$unitId = 1;
Endian::$defaultEndian = 5;

//date_default_timezone_set("Asia/Calcutta"); 
$current_DT=date("d-m-Y H:i:s");

/*Array variable initialize */
$ser_row1=[]; $chem_seq=[]; $no_channel1=[]; $chember_name=[]; $chember_id=[]; $hmi_type=[]; $hmi_type=[]; $hmi_ip_address=[]; $LuxUv=[];
/*Empty variable initialize*/
$ip=$chember=$ch_type='';

/*Company Name*/
$select_cmp="select company_name from  tbl_company";
$query_cmp=mysqli_query($dbm,$select_cmp);
$row_cmp=mysqli_fetch_assoc($query_cmp);
/*End Company Name*/

/* Check which chamber is active */
$CheckCon=NULL;
 $select_ip="select lux_uvB,lux_uv,hmi_ip_address,hmi_type,report_type,Chamber_cd,Chamber_Name,ip_address,chamber_seq,no_of_temp_channel from  chamber_master where active_flg=1 and ip_address='".$tranpip."'";
$ip_query=mysqli_query($dbm,$select_ip);
$ser_row1=$chem_seq=$no_channel1=$chember_name=$chember_id=$chamber_type=$hmi_type=$hmi_ip_addres=$LuxUv=[];
while($ser_row=mysqli_fetch_assoc($ip_query))
{
	

	$ser_row1[]=$ser_row['ip_address'];
	$chem_seq[]=$ser_row['chamber_seq'];
	$no_channel1[]=$ser_row['no_of_temp_channel'];
	$chember_name[]=$ser_row['Chamber_Name'];
	$chember_id[]=$ser_row['Chamber_cd'];
	$chamber_type[]=$ser_row['report_type'];
	$hmi_type[]=$ser_row['hmi_type'];
	$hmi_ip_address[]=$ser_row['hmi_ip_address'];
	$LuxUv[]=$ser_row['lux_uv'];
	$LuxUvB[]=$ser_row['lux_uvB'];
}
	
	//if(count($ser_row1)>0)
		if(!empty($ser_row1))
{
/*Chamber Chec End*/
for($CO=0;$CO<=count($ser_row1);$CO++) //Loop Start to received data from PLC using IP address
{
	$ping_ip=$ser_row1[$CO];
$ping_command = "ping -n 1 -w 3000 " . escapeshellarg($ping_ip);

// Execute the ping command
exec($ping_command, $ping_output, $ping_result);

// Check the ping result
if ($ping_result === 0) {
    // Check the output for unreachable messages
    $output_string = implode(" ", $ping_output);
   
    if (strpos($output_string, 'Destination host unreachable') !== false) {
       // echo "IP $ping_ip is not reachable!!." . PHP_EOL;
   } else {
        if($hmi_type[$CO]=="A") //Check PLC Type
	{
		if(!empty($chem_seq[$CO]))
		{
			/*Check Connection*/
			$ip=$ser_row1[$CO];
			$chember=$chem_seq[$CO];
			$ch_type=$chamber_type[$CO];
			$connection = BinaryStreamConnection::getBuilder()
						->setPort($port)
						->setHost($ip)
						->build();
			try{
        		$packet3 = new WriteSingleRegisterRequest(46, 5,$unitId); // 5 write for PLC connected to software 
        		$binaryData = $connection->connect()->sendAndReceive($packet3);
				//echo "connected"; echo '<br>';
				$CheckCon=1;
				}catch(Throwable $exception){
					$exception->getTraceAsString();
					$CheckCon=NULL;
					}
				finally{
					//$connection->close();			
				}
				
        	/*End Check Connection*/
			
			/*Read Print Frequencty*/
			//echo $CheckCon;
			
			
			
			if($CheckCon==1){
				
				
			$packet = new ReadHoldingRegistersRequest(299,1, $unitId);
			$PrintPF=NULL;
			try {
					$binaryData = $connection->connect()->sendAndReceive($packet);
					$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(299);
					
					foreach ($response as $address => $word)
					{
						 $PrintPF=$word->getUInt16();
					}
					
				} catch (Throwable $exception) {
					$PrintPF = null;
				}
			
			$packet = new ReadHoldingRegistersRequest(256, 6, $unitId);
			$result3 = [];
			try {
					$binaryData = $connection->connect()->sendAndReceive($packet);
					$log[] = 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1];
					$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(256);
					foreach ($response as $address => $word) {
					$doubleWord = isset($response[$address + 1]) ? $response->getDoubleWordAt($address) : null;
					$result3[$address] = $word->getInt16();
				}
				} catch (Throwable $exception) {
					$result3 = null;
					
				} finally {
				   // $connection->close();
				}
			
				 $current_date_cd=$result3[256].'-'.$result3[257].'-'.$result3[258].' '.$result3[259].':'.$result3[260].':'.$result3[261];
				 $current_date_cd=date('Y-m-d H:i:s',strtotime($current_date_cd));
				
			/*Update Print Frequencty*/
			$select_pf="select * from printfrequency_settings where chamber_seq='".$chember."'";
			$query_pf=mysqli_query($dbm,$select_pf);
			
			$row_pf=mysqli_num_rows($query_pf);
			echo mysqli_error($dbm);
			if($row_pf!="")
			{
				
				$update_pf="update printfrequency_settings set printfrequency_master='".$PrintPF."',frequency_date='".$current_date_cd."' where chamber_seq='".$chember."'";
				$query_updatePF=mysqli_query($dbm,$update_pf);
				
				
			}else{
				$insert_PF="insert into printfrequency_settings(chamber_seq,printfrequency_master,printfrequency_scanner,frequency_date)values('".$chember."','".$PrintPF."','','".$current_date_cd."')"; 
				$query_insertPF=mysqli_query($dbm,$insert_PF);
				echo mysqli_error($dbm);
				}
			
			/*End Update Print Frequencty*/	
			
			
			/* Master Data Retrive Start*/
			$TotalCountMaster=$MasterFRCount=$MasterSRCount=$MasterFRRetrive=$MasterSRRetrive=$TotalCountMaster1=$TotalRetriveCountMaster='';
			
			$packet = new ReadHoldingRegistersRequest(360, 1, $unitId); // Read Total Count Of Master Reading 
			try 
			{
				$binaryData = $connection->connect()->sendAndReceive($packet);
				$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(360);
				$resultMasterCount=[];
				foreach ($response as $address => $word)
				{
					$resultMasterCount[$address] = ['int16' => $word->getUInt16()];
				}
			}
			catch (Throwable $exception)
			{
				$resultMasterCount = null;
			} 
			$TotalCountMaster=$resultMasterCount[360]['int16'];  //Total count of Master Readting
			
			$TotalCountMaster1=$resultMasterCount[360]['int16']; //Total count of Master Readting for Reset
			if($TotalCountMaster!=0)
			{
				if($TotalCountMaster<=242) // Check Master Total Count is 242 or greater
				{
					$MasterFRCount=$TotalCountMaster;
					$MasterSRCount=0;
				}
				else
				{
					$MasterFRCount=242;
					$MasterFRCount1=242;
					$MasterSRCount=$TotalCountMaster-242;
				}
				
				
				$packet = new ReadHoldingRegistersRequest(362, 1, $unitId);
				$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
				$SecondRetriveCountMaster = '';
				try {
				$binaryData = $connection->connect()->sendAndReceive($packet);
				$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(362);
				foreach ($response as $address => $word)
						{
							$SecondRetriveCountMaster=$word->getUInt16();
						}
					} catch (Throwable $exception) {
						
						$SecondRetriveCountMaster = null;
						}
				
				
				if($MasterFRCount<=242 && $SecondRetriveCountMaster==0)
				{
					$RowRetriveMaster=0;
					
					/*Check Master First Recepi Starting Bit*/
					 $SelectRetriveBit="select * from coutner_record where ip_add='".$ip."' and Type='M'";
					$QueryRetrive=mysqli_query($dbm,$SelectRetriveBit);
					$RowRetriveMaster=mysqli_num_rows($QueryRetrive); 
					$StartBitMaster1=mysqli_fetch_assoc($QueryRetrive);
					if($RowRetriveMaster==0)
					{
						$packet = new ReadHoldingRegistersRequest(361, 1, $unitId);
						$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
						$TotalRetriveCountMasterMain = '';
						try {
						$binaryData = $connection->connect()->sendAndReceive($packet);
						$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(361);
						foreach ($response as $address => $word)
								{
									 $TotalRetriveCountMasterMain=$word->getUInt16();
								}
							} catch (Throwable $exception) {
								//$FRP=242;
								$TotalRetriveCountMasterMain = null;
								}
								
						if($TotalRetriveCountMasterMain==0 && $TotalRetriveCountMasterMain=='')
						{
							$InsertCountM="insert into  coutner_record (ip_add,StartCount1,StartCount2,Type,Last_DateTime) values ('".$ip."',0,0,'M','".$current_DT."') ";  
							$QueryCountM=mysqli_query($dbm,$InsertCountM);
							$InsertCountS="insert into  coutner_record (ip_add,StartCount1,StartCount2,Type,Last_DateTime) values ('".$ip."',0,0,'S','".$current_DT."') "; 
							$QueryCountS=mysqli_query($dbm,$InsertCountS);
							$InsertCountSLUX="insert into  coutner_record (ip_add,StartCount1,StartCount2,Type,Last_DateTime) values ('".$ip."',0,0,'L','".$current_DT."') "; 
							$QueryCountSLUX=mysqli_query($dbm,$InsertCountSLUX);
						}						
						
					}
					
					else
					{
						if($StartBitMaster1['StartCount1']==0)
						{
							$StartFor=0;
						}else{
							$StartFor=$StartBitMaster1['StartCount1'];
						}
						
						
						/*Check Counter Record Table and Retrive Bit which is greater Master 1RCP*/
						$packet = new ReadHoldingRegistersRequest(361, 1, $unitId);
						$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
						$TotalRetriveCounterRecord = '';
						try {
								$binaryData = $connection->connect()->sendAndReceive($packet);
								$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(361);
								foreach ($response as $address => $word)
								{
									$TotalRetriveCounterRecord=$word->getUInt16();
								}
							} catch (Throwable $exception) {
								$FRP=242;
								$TotalRetriveCounterRecord = null;
								}
						if($TotalRetriveCounterRecord>$StartFor)
						{
							$FirstCounterRecordUpdate="update coutner_record set StartCount1='".$TotalRetriveCounterRecord."' where ip_add='".$ip."' and  Type='M'"; 
							$FirstCounterRecordQuery=mysqli_query($dbm,$FirstCounterRecordUpdate);
							$StartFor=$TotalRetriveCounterRecord;
						}
						
						if($TotalRetriveCounterRecord==0 && $TotalRetriveCounterRecord<$StartFor)
						{
							$FirstCounterRecordUpdate="update coutner_record set StartCount1='0' where ip_add='".$ip."' and  Type='M'"; 
							$FirstCounterRecordQuery=mysqli_query($dbm,$FirstCounterRecordUpdate);
							$StartFor=$TotalRetriveCounterRecord;
						}
						
						/*if($TotalRetriveCounterRecord<$StartFor)
						{
							try{
									$packetRetriveMaster = new WriteSingleRegisterRequest(361, $StartFor,$unitId);//Master First Recepi Retrive count write
									$connection->connect()->sendAndReceive($packetRetriveMaster);	
								}catch(Throwable $exception)
								{
									//echo 'connection failed';
								}
						}*/
						/*Close counter record Master*/
					
						for($FRP=$StartFor;$FRP<=$MasterFRCount;$FRP++)
						{
							try{
							$packet3 = new WriteSingleRegisterRequest(46, 5,$unitId); // 5 write for PLC connected to software 
							$binaryData = $connection->connect()->sendAndReceive($packet3);
							//echo "connected"; echo '<br>';
							$CheckCon=1;
							}catch(Throwable $exception){
								$exception->getTraceAsString();
								$CheckCon=NULL;
								}
							finally{
								//$connection->close();			
							}
							
							respons();
							
							$SelectRetriveBit1="select StartCount1 from coutner_record where ip_add='".$ip."' and Type='M'"; 
							$QueryRetrive1=mysqli_query($dbm,$SelectRetriveBit1);
							$CheckRetriveBitMaster1=mysqli_fetch_assoc($QueryRetrive1);
							$MasterFRRetrive=$CheckRetriveBitMaster1['StartCount1']+1;	
							
							$packet = new ReadHoldingRegistersRequest(361, 1, $unitId);
							$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
							$TotalRetriveCount = '';
							try {
									$binaryData = $connection->connect()->sendAndReceive($packet);
									$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(361);
									foreach ($response as $address => $word)
									{
										$TotalRetriveCount=$word->getUInt16();
									}
								} catch (Throwable $exception) {
									$FRP=242;
									$TotalRetriveCount = null;
									}
							
							if($TotalRetriveCountMaster<=242 && $MasterFRRetrive<=$MasterFRCount)
							{
								$MasterFRdate1=$MasterFRdate=$Master_temp_set=$Master_hum_set=$Master_temp_val=$Master_hum_val=$countR='';
								
								
								try{
									$packetRetriveMaster = new WriteSingleRegisterRequest(361, $MasterFRRetrive,$unitId);//Master First Recepi Retrive count write
									$connection->connect()->sendAndReceive($packetRetriveMaster);	
								}catch(Throwable $exception)
								{
									//echo 'connection failed';
								}
								
								$packet = new ReadHoldingRegistersRequest(0, 10, $unitId);
								$MasterFRresult = [];
								try {
										$binaryData = $connection->connect()->sendAndReceive($packet);
										$log[] = 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1];
										$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(0);
										foreach ($response as $address => $word) {
										$doubleWord = isset($response[$address + 1]) ? $response->getDoubleWordAt($address) : null;
										$MasterFRresult[$address] = $word->getInt16(); // Take All master first recepi data in array
									}
									} catch (Throwable $exception) {
										$MasterFRresult = null;
									} 
								$MasterFRdate=$MasterFRresult[0].'-'.$MasterFRresult[1].'-'.$MasterFRresult[2].' '.$MasterFRresult[3].':'.$MasterFRresult[4].':00';
								$MasterFRdate1=date('Y-m-d H:i:s', strtotime($MasterFRdate));
								
								/* This live data pass to Master Real Time data */
									$Master_temp_set=$MasterFRresult[6]/10;
									$Master_hum_set=$MasterFRresult[7]/10;
									$Master_temp_val=$MasterFRresult[8]/10;
									$Master_hum_val=$MasterFRresult[9]/10;
									
									$Mster_year='';
									$Mster_year=date('Y', strtotime($MasterFRdate1));
								/* This live data pass to Master Real Time data */
								$Row_Double='';
								$FirstMasterCount=0;
								$Select_Double1="select Record_Date_Time from controller_details where chamber_seq= '".$chember."' and Record_Date_Time='".$MasterFRdate1."' ORDER BY  Record_Date_Time DESC"; 
								$QueryDouble1=mysqli_query($dbm,$Select_Double1);
								$Row_Double=mysqli_fetch_assoc($QueryDouble1);
								$FirstMasterCount=mysqli_num_rows($QueryDouble1);
								/*$CheckMasterTimeFrequ=0;
								$CheckMasterTimeFrequ=date('Y-m-d H:i',strtotime($Row_Double['Record_Date_Time']));
								$time = new DateTime($CheckMasterTimeFrequ);
								$time->add(new DateInterval('PT' . $PrintPF . 'M'));
								$stamp = $time->format('Y-m-d H:i');
								$firstRCMSTRdatetime=0;
								$firstRCMSTRdatetime=date('Y-m-d H:i',strtotime($MasterFRdate1));*/
								
								if($MasterFRresult[0]!='0' && $MasterFRresult[0]!='1970' && $Mster_year!='1970' && $MasterFRRetrive!='' && $MasterFRRetrive!=0 && $MasterFRRetrive!='-1' && $Row_Double['Record_Date_Time']!=$MasterFRdate1 && $FirstMasterCount==0 && $PrintPF!=0 && $PrintPF!='') //check year value 0 or 1970 then retrive bit will be minus
								{
									$insert="insert into controller_details (Temperature_Value,Temperature_SetPoint,Humidity_Value,Humidity_SetPoint,Record_Date_Time,chamber_seq,print_frequency) VALUES ('".$Master_temp_val."','".$Master_temp_set."','".$Master_hum_val."','".$Master_hum_set."','".$MasterFRdate1."','".$chember."','".$PrintPF."')";  
									mysqli_query($dbm,$insert) or die(mysqli_error($dbm));
									
									$FirstUpdate="update coutner_record set StartCount1='".$MasterFRRetrive."' where ip_add='".$ip."' and  Type='M'"; 
									$FirstQuery=mysqli_query($dbm,$FirstUpdate);
									
								}
								elseif($Row_Double['Record_Date_Time']==$MasterFRdate1)
								{
									/*try{
											$MasterFRRetrive1=1;
											$packetFRM21 = new WriteSingleRegisterRequest(361, 1,$unitId);
											$connection->connect()->sendAndReceive($packetFRM21);
											$FirstUpdateM1="update coutner_record set StartCount1='".$MasterFRRetrive1."' where ip_add='".$ip."' and  Type='M'"; 
											$FirstQuery=mysqli_query($dbm,$FirstUpdateM1);
										
									}catch(Throwable $exception){}*/
									
								}	
								else
								{ 
									/*$packetFRM1 = new ReadHoldingRegistersRequest(361, 1, $unitId); // Read Last retrive bit for increment
									$resultFRM1 = '';
									try 
									{
										$binaryData = $connection->connect()->sendAndReceive($packetFRM1);
										$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(361);
										foreach ($response as $address => $word)
										{
											$resultFRM1=$word->getUInt16();
										}
										
									} catch (Throwable $exception) {
										$resultFRM1 = null;
										} 
									$countR1=$resultFRM1;
									$MasterFRRetrive=$countR1-1; // Decrement retrive bit
									
									
									try{
										if($MasterFRRetrive!='-1')
										{
											$packetFRM2 = new WriteSingleRegisterRequest(361, $MasterFRRetrive,$unitId);
											$connection->connect()->sendAndReceive($packetFRM2);
											 $FirstUpdateM="update coutner_record set StartCount1='".$MasterFRRetrive."' where ip_add='".$ip."' and  Type='M'";
											$FirstQuery=mysqli_query($dbm,$FirstUpdateM);
										}
									}catch(Throwable $exception){}*/
								}	
								
								
							}
							//sleep(1);
							usleep(200000);
						}
					}
				}
				
						$packet = new ReadHoldingRegistersRequest(361, 1, $unitId);
						$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
						$TotalRetriveCountMasterMain = '';
						try {
						$binaryData = $connection->connect()->sendAndReceive($packet);
						$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(361);
						foreach ($response as $address => $word)
								{
									 $TotalRetriveCountMasterMain=$word->getUInt16();
								}
							} catch (Throwable $exception) {
								//$FRP=242;
								$TotalRetriveCountMasterMain = null;
								}
						
				
				if($TotalRetriveCountMasterMain==242)
				{  
					$MasterSecondValue=0;
					
					if($SecondRetriveCountMaster!=0)
					{
						$MasterSecondValue=$SecondRetriveCountMaster;
					}
					
					for($SRM=$MasterSecondValue;$SRM<=$MasterSRCount;$SRM++)
					{
						try{
							$packet3 = new WriteSingleRegisterRequest(46, 5,$unitId); // 5 write for PLC connected to software 
							$binaryData = $connection->connect()->sendAndReceive($packet3);
							//echo "connected"; echo '<br>';
							$CheckCon=1;
							}catch(Throwable $exception){
								$exception->getTraceAsString();
								$CheckCon=NULL;
								}
							finally{
								//$connection->close();			
							}
						respons();
						if($TimeChange=="23:55")
						{
							SetDateTime();
						}
							
						$MasterSRdate1=$MasterSRdate=$Master_temp_set=$Master_hum_set=$Master_temp_val=$Master_hum_val=$countR='';
						
						$SelectRetriveBit2="select StartCount2 from coutner_record where ip_add='".$ip."' and Type='M'";
						$QueryRetrive2=mysqli_query($dbm,$SelectRetriveBit2);
						$CheckRetriveBitMaster2=mysqli_fetch_assoc($QueryRetrive2);
						$MasterSRRetrive=$CheckRetriveBitMaster2['StartCount2']+1;
						
						$packet = new ReadHoldingRegistersRequest(362, 1, $unitId);
						$TotalRetriveCount1 = '';
						try {
						
						$binaryData = $connection->connect()->sendAndReceive($packet);
						$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(362);
						foreach ($response as $address => $word)
								{
									$TotalRetriveCount1=$word->getUInt16();
								}
							} catch (Throwable $exception) {
								$SRM=242;
								$TotalRetriveCount1 = null;
								}
						$TotalRetriveCountMaster1=$TotalRetriveCount1;
						if($TotalRetriveCountMaster1<=242 && $MasterSRRetrive<=$MasterSRCount )
						{
							
							try{
								$packetRetriveMaster = new WriteSingleRegisterRequest(362, $MasterSRRetrive,$unitId);//Master First Recepi Retrive count write
							$connection->connect()->sendAndReceive($packetRetriveMaster);	
							}catch(Throwable $exception)
							{}
							
							$packet = new ReadHoldingRegistersRequest(14, 10, $unitId);
							$MasterSRresult = [];
							try {
									$binaryData = $connection->connect()->sendAndReceive($packet);
									$log[] = 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1];
									$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(14);
									foreach ($response as $address => $word) {
									$doubleWord = isset($response[$address + 1]) ? $response->getDoubleWordAt($address) : null;
									$MasterSRresult[$address] = $word->getInt16(); // Take All master first recepi data in array
								}
								} catch (Throwable $exception) {
									$MasterSRresult = null;
								} 
								$Master_SYear='';
							$MasterSRdate=$MasterSRresult[14].'-'.$MasterSRresult[15].'-'.$MasterSRresult[16].' '.$MasterSRresult[17].':'.$MasterSRresult[18].':00';
							$MasterSRdate1=date('Y-m-d H:i:s', strtotime($MasterSRdate));
							
							/* This live data pass to Master Real Time data */
								$Master_temp_set=$MasterSRresult[20]/10;
								$Master_hum_set=$MasterSRresult[21]/10;
								$Master_temp_val=$MasterSRresult[22]/10;
								$Master_hum_val=$MasterSRresult[23]/10;
								
							/* This live data pass to Master Real Time data */
							
							$Master_SYear=date('Y', strtotime($MasterSRdate1));
							
							$Row_Double2='';
							$SecondmasterCount=0;
							$Select_Double2="select Record_Date_Time from controller_details where chamber_seq= '".$chember."' and Record_Date_Time='".$MasterSRdate1."' ORDER BY  Record_Date_Time DESC ";
							$QueryDouble2=mysqli_query($dbm,$Select_Double2);
							$Row_Double2=mysqli_fetch_assoc($QueryDouble2);
							$SecondmasterCount=mysqli_num_rows($QueryDouble2);
							if($MasterSRresult[14]!='0' && $MasterSRresult[15]!='1970' && $Master_SYear!='1970' && $MasterSRRetrive!='-1' && $Row_Double2['Record_Date_Time']!=$MasterSRdate1 && $SecondmasterCount==0 &&  $PrintPF!=0 && $PrintPF!='') //check year value 0 or 1970 then retrive bit will be minus
							{
								 $insertSM="insert into controller_details (Temperature_Value,Temperature_SetPoint,Humidity_Value,Humidity_SetPoint,Record_Date_Time,chamber_seq,print_frequency) VALUES ('".$Master_temp_val."','".$Master_temp_set."','".$Master_hum_val."','".$Master_hum_set."','".$MasterSRdate1."','".$chember."','".$PrintPF."')"; 
								mysqli_query($dbm,$insertSM) or die(mysqli_error($dbm));
								
								$FirstUpdateS="update coutner_record set StartCount2='".$MasterSRRetrive."' where ip_add='".$ip."' and  Type='M'"; 
								$FirstQuery=mysqli_query($dbm,$FirstUpdateS);
							}
							elseif($Row_Double2['Record_Date_Time']==$MasterSRdate1)
							{
								/*try{
										$MasterSRRetrive1=1;
										$packetSRM21 = new WriteSingleRegisterRequest(362, 1,$unitId);
										$connection->connect()->sendAndReceive($packetSRM21);
										$FirstUpdateM="update coutner_record set StartCount2='".$MasterSRRetrive1."' where ip_add='".$ip."' and  Type='M'"; 
										$FirstQuery=mysqli_query($dbm,$FirstUpdateM);
									
								}catch(Throwable $exception){}*/
								
								
							}
							else
							{	
								/*$countR1=='';
								$packetSRM1 = new ReadHoldingRegistersRequest(362, 1, $unitId); // Read Last retrive bit for increment
								$resultSRM1 = '';
								try 
								{
									$binaryData = $connection->connect()->sendAndReceive($packetSRM1);
									$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(362);
									foreach ($response as $address => $word)
									{
										$resultSRM1=$word->getUInt16();
									}
									
								} catch (Throwable $exception) {
									$resultSRM1 = null;
									} 
								$countR1=$resultSRM1;
								$MasterSRRetrive=$countR1-1; // Decrement retrive bit
								
								
								try{
									if($MasterSRRetrive!='-1')
									{
										$packetSRM2 = new WriteSingleRegisterRequest(362, $MasterSRRetrive,$unitId);
										$connection->connect()->sendAndReceive($packetSRM2);
										$FirstUpdateM="update coutner_record set StartCount2='".$MasterSRRetrive."' where ip_add='".$ip."' and  Type='M'"; 
										$FirstQuery=mysqli_query($dbm,$FirstUpdateM);
									}
								}catch(Throwable $exception){}*/
							}
						}
						//sleep(1);
						usleep(200000);
					
					}
				}
				
				$packet = new ReadHoldingRegistersRequest(360, 1, $unitId); // Read Total Count Of Master Reading 
				try 
				{
					$binaryData = $connection->connect()->sendAndReceive($packet);
					$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(360);
					$resultMasterCount=[];
					foreach ($response as $address => $word)
					{
						$resultMasterCount[$address] = ['int16' => $word->getUInt16()];
					}
				}
				catch (Throwable $exception)
				{
					$resultMasterCount = null;
				} 
			
				$TotalCountMaster1=$resultMasterCount[360]['int16']; //Total count of Master Readting for Reset
				
				$packet = new ReadHoldingRegistersRequest(361, 1, $unitId);
				$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
				$resultFRSCM = [];
				try {
				$binaryData = $connection->connect()->sendAndReceive($packet);
				$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(361);
				foreach ($response as $address => $word) {
						$resultFRSCM[$address] = $word->getInt16();
						}
					} catch (Throwable $exception) {
						$resultFRSCM = null;
						} finally {
					   // $connection->close();
					}
					
				$packet = new ReadHoldingRegistersRequest(362, 1, $unitId);
				$resultSRSCM = [];
				try {
				$binaryData = $connection->connect()->sendAndReceive($packet);
				$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(362);
				foreach ($response as $address => $word) {
						$resultSRSCM[$address] = $word->getInt16();
						}
					} catch (Throwable $exception) {
						$resultSRSCM = null;
						} finally {
					   // $connection->close();
					}
				$TotalRetriveMasterCount='';
				$TotalRetriveMasterCount=$resultFRSCM[361]+$resultSRSCM[362]; 
				if($TotalCountMaster1>=40 &&$TotalRetriveMasterCount>=40)
				{
					if($TotalCountMaster1==$TotalRetriveMasterCount)
					{
						try{
						$packet3 = new WriteSingleRegisterRequest(363, 1,$unitId);
						$binaryData = $connection->connect()->sendAndReceive($packet3);

						/*$packet3 = new WriteSingleRegisterRequest(363, 0,$unitId);
						$binaryData = $connection->connect()->sendAndReceive($packet3);*/
						$packet = new ReadHoldingRegistersRequest(360, 1, $unitId); // Read Total Count Of Master Reading 
							try 
							{
								$binaryData = $connection->connect()->sendAndReceive($packet);
								$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(360);
								$resultMasterCount=[];
								foreach ($response as $address => $word)
								{
									$resultMasterCount[$address] = ['int16' => $word->getUInt16()];
								}
							}
							catch (Throwable $exception)
							{
								$resultMasterCount = null;
							} 
						
							$TotalCountMaster1=$resultMasterCount[360]['int16']; //Total count of Master Readting for Reset
							
							$packet = new ReadHoldingRegistersRequest(361, 1, $unitId);
							$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
							$resultFRSCM = [];
							try {
							$binaryData = $connection->connect()->sendAndReceive($packet);
							$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(361);
							foreach ($response as $address => $word) {
									$resultFRSCM[$address] = $word->getInt16();
									}
								} catch (Throwable $exception) {
									$resultFRSCM = null;
									} finally {
								   // $connection->close();
								}
								
							$packet = new ReadHoldingRegistersRequest(362, 1, $unitId);
							$resultSRSCM = [];
							try {
							$binaryData = $connection->connect()->sendAndReceive($packet);
							$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(362);
							foreach ($response as $address => $word) {
									$resultSRSCM[$address] = $word->getInt16();
									}
								} catch (Throwable $exception) {
									$resultSRSCM = null;
									} finally {
								   // $connection->close();
								}
							$TotalRetriveMasterCount2='';
							$TotalRetriveMasterCount2=$resultFRSCM[361]+$resultSRSCM[362]; 
							usleep(600000);
								if($TotalCountMaster1==0 && $TotalRetriveMasterCount2==0)
								{
									$FirstUpdate1="update coutner_record set StartCount1='0',StartCount2='0' where ip_add='".$ip."' and  Type='M'";
									$FirstQuery1=mysqli_query($dbm,$FirstUpdate1);
								}
							} catch (Throwable $exception) { }
					}
				}
			}
			/* Master Data Retrive End*/
			
			/*Scanner Data Retrive Star*/
			$ScannerTotalCount=$ScannerTotalCount1=$ScannerSRCount=$ScannerFRCount=$ScannerFRRetrive=$ScannerFRdatetime=$TotalRetriveCountScanner='';
			$packet = new ReadHoldingRegistersRequest(364, 1, $unitId);
			$Scannerresult = [];
			try 
			{
			$binaryData = $connection->connect()->sendAndReceive($packet);
			$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(364);
			foreach ($response as $address => $word) {
			$Scannerresult[$address] = ['int16' => $word->getUInt16()];
			}
			} catch (Throwable $exception) {
			$ScannerFRresult = null;
			} 
			 $ScannerTotalCount=$Scannerresult[364]['int16']; //Scanner Total Count
		
			$ScannerTotalCount1=$Scannerresult[364]['int16']; //Scanner Total Count for reset
			
			if($ScannerTotalCount!=0)
			{
				if($ScannerTotalCount<=242) // Check Scanner Total Count is 242 or greater
				{
					$ScannerFRCount=$ScannerTotalCount;
					$ScannerSRCount=0;
				}
				else
				{
					$ScannerFRCount=242;
					$ScannerFRCount1=242;
					$ScannerSRCount=$ScannerTotalCount-242;
				}
				$packet = new ReadHoldingRegistersRequest(366, 1, $unitId);
				$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
				$SecondRetriveCount = '';
				try {
				$binaryData = $connection->connect()->sendAndReceive($packet);
				$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(366);
				foreach ($response as $address => $word)
						{
							$SecondRetriveCount=$word->getUInt16();
						}
					} catch (Throwable $exception) {
						
						$SecondRetriveCount = null;
						}
				
				
				
				
				if($ScannerFRCount<=242 && $SecondRetriveCount==0)
				{
					
					$SelectRetriveBitS="select StartCount1 from coutner_record where ip_add='".$ip."' and Type='S'";
					$QueryRetriveS=mysqli_query($dbm,$SelectRetriveBitS);
					$CheckRetriveBitScanner=mysqli_fetch_assoc($QueryRetriveS);
					$StartForScanner=$CheckRetriveBitScanner['StartCount1'];
						
					/*Check Counter Record Table and Retrive Bit which is greater Scanner 1RCP*/
						$packet = new ReadHoldingRegistersRequest(365, 1, $unitId);
						$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
						$TotalRetriveCounterRecordScanner = '';
						try {
								$binaryData = $connection->connect()->sendAndReceive($packet);
								$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(365);
								foreach ($response as $address => $word)
								{
									$TotalRetriveCounterRecordScanner=$word->getUInt16();
								}
							} catch (Throwable $exception) {
								
								$TotalRetriveCounterRecordScanner = null;
								}
						if($TotalRetriveCounterRecordScanner>$StartForScanner)
						{
							$FirstCounterRecordUpdate="update coutner_record set StartCount1='".$TotalRetriveCounterRecordScanner."' where ip_add='".$ip."' and  Type='S'"; 
							$FirstCounterRecordQuery=mysqli_query($dbm,$FirstCounterRecordUpdate);
							$StartForScanner=$TotalRetriveCounterRecordScanner;
						}
						
						if($TotalRetriveCounterRecordScanner==0 && $TotalRetriveCounterRecordScanner<$StartForScanner)
						{
							$FirstCounterRecordUpdate="update coutner_record set StartCount1='0' where ip_add='".$ip."' and  Type='S'"; 
							$FirstCounterRecordQuery=mysqli_query($dbm,$FirstCounterRecordUpdate);
							$StartForScanner=$TotalRetriveCounterRecordScanner;
						}
						
						/*if($TotalRetriveCounterRecordScanner<$StartForScanner)
						{
							try{
									$packetRetriveMaster = new WriteSingleRegisterRequest(365, $StartForScanner,$unitId);//Scanner First Recepi Retrive count write
									$connection->connect()->sendAndReceive($packetRetriveMaster);	
								}catch(Throwable $exception)
								{
									//echo 'connection failed';
								}
						}*/
						/*Close counter record Scanner*/
					
					
					
					
					
					
					for($FRS=0;$FRS<=$ScannerFRCount;$FRS++)
					{
						try{
						$packet3 = new WriteSingleRegisterRequest(46, 5,$unitId); // 5 write for PLC connected to software 
						$binaryData = $connection->connect()->sendAndReceive($packet3);
						//echo "connected"; echo '<br>';
						$CheckCon=1;
						}catch(Throwable $exception){
							$exception->getTraceAsString();
							$CheckCon=NULL;
							}
						finally{
							//$connection->close();			
						}
						respons();
						if($TimeChange=="23:55")
						{
							SetDateTime();
						}
							
						$SelectRetriveBitS="select StartCount1 from coutner_record where ip_add='".$ip."' and Type='S'";
						$QueryRetriveS=mysqli_query($dbm,$SelectRetriveBitS);
						$CheckRetriveBitScanner=mysqli_fetch_assoc($QueryRetriveS);
						$ScannerFRRetrive=$CheckRetriveBitScanner['StartCount1']+1;
						
						$packet = new ReadHoldingRegistersRequest(365, 1, $unitId);
						$TotalRetriveCountSca = '';
						try {
						$binaryData = $connection->connect()->sendAndReceive($packet);
						$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(365);
						foreach ($response as $address => $word)
								{
									$TotalRetriveCountSca=$word->getUInt16();
								}
							} catch (Throwable $exception) {
								$FRS=242;
								$TotalRetriveCountSca = null;
								}
						$TotalRetriveCountScanner=$TotalRetriveCountSca;
						if($TotalRetriveCountScanner<243 && $ScannerFRRetrive<=$ScannerFRCount)
						{
							$countS2='';
							
							try{
								$packetFRScanner = new WriteSingleRegisterRequest(365,$ScannerFRRetrive,$unitId);
							$connection->connect()->sendAndReceive($packetFRScanner);
							}catch(Throwable $exception){}
							
							$packetFRScanner= new ReadHoldingRegistersRequest(78, 32, $unitId);
							$ScannerFRresult = [];
							try 
							{
								$binaryData = $connection->connect()->sendAndReceive($packetFRScanner);
								$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(78);
								foreach ($response as $address => $word) {
								$ScannerFRresult[$address] = $word->getInt16();
								}
							} catch (Throwable $exception) {
								
									$ScannerFRresult = null;
									}
									$ScannerFRdatetime1=$Scanner_FYear='';
							$ScannerFRdatetime=$ScannerFRresult[78].'-'.$ScannerFRresult[79].'-'.$ScannerFRresult[80].' '.$ScannerFRresult[81].':'.$ScannerFRresult[82].':00'; 
							$ScannerFRdatetime1=date('Y-m-d H:i:s', strtotime($ScannerFRdatetime));
							
							$Scanner_FYear=date('Y', strtotime($ScannerFRdatetime1));
							
							
							$Row_Double3='';
							$FirstScannerCount=0;
							$Select_Double3="select Record_Date_Time from scanner_details where chamber_seq= '".$chember."' and Record_Date_Time='".$ScannerFRdatetime1."' ORDER BY  Record_Date_Time DESC ";
							$QueryDouble3=mysqli_query($dbm,$Select_Double3);
							$Row_Double3=mysqli_fetch_assoc($QueryDouble3);
							$FirstScannerCount=mysqli_num_rows($QueryDouble3);
							/*$CheckMasterTimeFrequsC=0;
							$CheckMasterTimeFrequsC=date('Y-m-d H:i',strtotime($Row_Double3['Record_Date_Time']));
							
							$PrintPFsC=$PrintPF;
							$timesC = new DateTime($CheckMasterTimeFrequsC);
							$timesC->add(new DateInterval('PT' . $PrintPFsC . 'M'));
							$stampsC = $timesC->format('Y-m-d H:i');
							$firstRCMSTRdatetimesC=0;
							$firstRCMSTRdatetimesC=date('Y-m-d H:i',strtotime($ScannerFRdatetime1));*/
						
							
							if($ScannerFRresult[78]!='0' && $ScannerFRresult[78]!='1970' && $Scanner_FYear!='1970' && $ScannerFRRetrive!=0&& $ScannerFRRetrive!='' && $ScannerFRRetrive!='-1' && $Row_Double3['Record_Date_Time']!=$ScannerFRdatetime1 && $FirstScannerCount==0 &&  $PrintPF!=0 && $PrintPF!='')
							{
								$Updateno_channel1=$ch=$scan_temp=$scan_hum='';
								$z=1;$x=86;$y=87;
								$Updateno_channel1=$no_channel1[$CO]/2; 
								for($k1=0;$k1<$Updateno_channel1;$k1++)
								{
									$ch="CH".$z;
									$scan_temp=number_format((float)($ScannerFRresult[84])/10, 1, '.', '');
									$scan_hum=number_format((float)($ScannerFRresult[85])/10, 1, '.', '');
									$insertFRScanner="insert into scanner_details (Temperature_Value,humidity_value,Record_Date_Time,chamber_seq,component_cd,Temperature_setpoint,Humidity_setpoint,print_frequency) VALUES ('".number_format((float)($ScannerFRresult[$x])/10, 1, '.', '')."','".number_format((float)($ScannerFRresult[$y])/10, 1, '.', '')."','".$ScannerFRdatetime1."','".$chember."','".$ch."','".$scan_temp."','".$scan_hum."','".$PrintPF."')"; 
									mysqli_query($dbm,$insertFRScanner) or die(mysqli_error($dbm)); 
									$z++; $x=$x+2; $y=$y+2;
								}
								$ScannerFRdatetime1='';
								
								$FirstUpdateScanner="update coutner_record set StartCount1='".$ScannerFRRetrive."' where ip_add='".$ip."' and  Type='S'"; 
								$FirstQuery2=mysqli_query($dbm,$FirstUpdateScanner);
								
							}
							elseif($Row_Double3['Record_Date_Time']==$ScannerFRdatetime1)
							{
								
								/*$MinusFirstRtriveBit1=1;
								try{
									
										$packet1 = new WriteSingleRegisterRequest(365,1,$unitId);
										$connection->connect()->sendAndReceive($packet1);
									$FirstUpdate1="update coutner_record set StartCount1='".$MinusFirstRtriveBit1."' where ip_add='".$ip."' and  Type='S'"; 
										$FirstQuery=mysqli_query($dbm,$FirstUpdate1);
										
									}catch(Throwable $exception){}*/
								
							}
							else
							{
								/*$FirstRtriveBit='';
								$packet = new ReadHoldingRegistersRequest(365, 1, $unitId);
								$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
								$resultFRSC = '';
								try {
								$binaryData = $connection->connect()->sendAndReceive($packet);
								$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(365);
								foreach ($response as $address => $word)
									{
										$resultFRSC=$word->getUInt16();
									}
								
									} catch (Throwable $exception) {
										$resultFRSC = null;
										}
									
									$FirstRtriveBit=$resultFRSC;
									$MinusFirstRtriveBit=$FirstRtriveBit-1;
									
									
										try{
											if($MinusFirstRtriveBit!='-1')
											{
												$packet1 = new WriteSingleRegisterRequest(365,$MinusFirstRtriveBit,$unitId);
												$connection->connect()->sendAndReceive($packet1);
												$FirstUpdate="update coutner_record set StartCount1='".$MinusFirstRtriveBit."' where ip_add='".$ip."' and  Type='S'"; 
												$FirstQuery=mysqli_query($dbm,$FirstUpdate);
											}
										}catch(Throwable $exception){}
									break;*/
							}
						}
						//sleep(1);
						usleep(200000);
					}
				}
				
				
				$packet = new ReadHoldingRegistersRequest(365, 1, $unitId);
						$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
						$TotalRetriveCountScannerMain = '';
						try {
						$binaryData = $connection->connect()->sendAndReceive($packet);
						$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(365);
						foreach ($response as $address => $word)
								{
									$TotalRetriveCountScannerMain=$word->getUInt16();
								}
							} catch (Throwable $exception) {
								//$FRP=242;
								$TotalRetriveCountScannerMain = null;
								}
				
				
				if($TotalRetriveCountScannerMain==242)
				{
					$ScannerRetriveValue=0;
					if($SecondRetriveCount!=0)
					{
						$ScannerRetriveValue=$SecondRetriveCount;
					}
					
					for($SRS=$ScannerRetriveValue;$SRS<=$ScannerSRCount;$SRS++)
					{	
				
						try{
						$packet3 = new WriteSingleRegisterRequest(46, 5,$unitId); // 5 write for PLC connected to software 
						$binaryData = $connection->connect()->sendAndReceive($packet3);
						//echo "connected"; echo '<br>';
						$CheckCon=1;
						}catch(Throwable $exception){
							$exception->getTraceAsString();
							$CheckCon=NULL;
							}
						finally{
							//$connection->close();			
						}
						
						respons();
						if($TimeChange=="23:55")
						{
							SetDateTime();
						}	
						$ScannerSRRetrive=$TotalRetriveCountScanner1=$ScannerSRdatetime='';
						
						$SelectRetriveBitS1="select StartCount2 from coutner_record where ip_add='".$ip."' and Type='S'";
						$QueryRetriveS1=mysqli_query($dbm,$SelectRetriveBitS1);
						$CheckRetriveBitScanner1=mysqli_fetch_assoc($QueryRetriveS1);
						$ScannerSRRetrive=$CheckRetriveBitScanner1['StartCount2']+1;
						
						$packet = new ReadHoldingRegistersRequest(366, 1, $unitId);
						$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
						$TotalRetriveCountSca1 = '';
						try {
						$binaryData = $connection->connect()->sendAndReceive($packet);
						$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(366);
						foreach ($response as $address => $word) {
								$TotalRetriveCountSca1= $word->getInt16();
								}
							} catch (Throwable $exception) {
								$SRS=242;
								$TotalRetriveCountSca1 = null;
								}
						$TotalRetriveCountScanner1=$TotalRetriveCountSca1;
						if($TotalRetriveCountScanner1<=242 && $ScannerSRRetrive<=$ScannerSRCount)
						{
							$ScannerSRdatetime1=$countS2='';
							
							try{
								$packetSRScanner = new WriteSingleRegisterRequest(366,$ScannerSRRetrive,$unitId);
							$connection->connect()->sendAndReceive($packetSRScanner);
							}catch(Throwable $exception){}
							$packetFRScanner= new ReadHoldingRegistersRequest(110, 32, $unitId);
							$ScannerSRresult = [];
							try 
							{
								$binaryData = $connection->connect()->sendAndReceive($packetFRScanner);
								$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(110);
								foreach ($response as $address => $word) {
								$ScannerSRresult[$address] = $word->getInt16();
								}
							} catch (Throwable $exception) {
								$SRS=$ScannerSRCount;
									$ScannerSRresult = null;
									}
									$Scanner_SYear='';
							$ScannerSRdatetime=$ScannerSRresult[110].'-'.$ScannerSRresult[111].'-'.$ScannerSRresult[112].' '.$ScannerSRresult[113].':'.$ScannerSRresult[114].':00'; 
							$ScannerSRdatetime1=date('Y-m-d H:i:s', strtotime($ScannerSRdatetime));
							
							$Scanner_SYear=date('Y', strtotime($ScannerSRdatetime1));
							
							$Row_Double4='';
							$SecondScannerCount=0;
							$Select_Double4="select Record_Date_Time from scanner_details  where chamber_seq= '".$chember."' and Record_Date_Time='".$ScannerSRdatetime1."' ORDER BY  Record_Date_Time DESC ";
							$QueryDouble4=mysqli_query($dbm,$Select_Double4);
							$Row_Double4=mysqli_fetch_assoc($QueryDouble4);
							$SecondScannerCount=mysqli_num_rows($QueryDouble4);
							if($ScannerSRresult[110]!='0' && $ScannerSRresult[110]!='1970' && $Scanner_SYear!='1970' && $ScannerSRRetrive!='-1' && $Row_Double4['Record_Date_Time']!=$ScannerSRdatetime1 && $SecondScannerCount==0 &&  $PrintPF!=0 && $PrintPF!='')
							{
								$Updateno_channel1=$ch=$scan_temp=$scan_hum='';
								$z=1;$x=118;$y=119;
								$Updateno_channel1=$no_channel1[$CO]/2; 
								for($k1=0;$k1<$Updateno_channel1;$k1++)
								{
									$ch="CH".$z;
									$scan_temp=number_format((float)($ScannerSRresult[116])/10, 1, '.', '');
									$scan_hum=number_format((float)($ScannerSRresult[117])/10, 1, '.', '');
									$insertFRScanner="insert into scanner_details (Temperature_Value,humidity_value,Record_Date_Time,chamber_seq,component_cd,Temperature_setpoint,Humidity_setpoint,print_frequency) VALUES ('".number_format((float)($ScannerSRresult[$x])/10, 1, '.', '')."','".number_format((float)($ScannerSRresult[$y])/10, 1, '.', '')."','".$ScannerSRdatetime1."','".$chember."','".$ch."','".$scan_temp."','".$scan_hum."','". $PrintPF."')"; 
									mysqli_query($dbm,$insertFRScanner) or die(mysqli_error($dbm)); 
									$z++; $x=$x+2; $y=$y+2;
								}
								
								
								$FirstUpdateScanner2="update coutner_record set StartCount2='".$ScannerSRRetrive."' where ip_add='".$ip."' and  Type='S'"; 
								$FirstQuery2=mysqli_query($dbm,$FirstUpdateScanner2);
								
							}
							elseif($Row_Double4['Record_Date_Time']==$ScannerSRdatetime1)
							{
								/*try{
										$MinusSecondRtriveBit1=1;
										$packet1 = new WriteSingleRegisterRequest(366,1,$unitId);
										$connection->connect()->sendAndReceive($packet1);
										 $FirstUpdate2="update coutner_record set StartCount2='".$MinusSecondRtriveBit1."' where ip_add='".$ip."' and  Type='S'"; 
										$FirstQuery=mysqli_query($dbm,$FirstUpdate2);
										
									}catch(Throwable $exception){}*/
							}
							else
							{
								/*$FirstRtriveBit=$MinusSecondRtriveBit='';
								$packet = new ReadHoldingRegistersRequest(366, 1, $unitId);
								$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
								$resultFRSC = '';
								try {
								$binaryData = $connection->connect()->sendAndReceive($packet);
								$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(366);
								foreach ($response as $address => $word)
									{
										$resultFRSC=$word->getUInt16();
									}
								
									} catch (Throwable $exception) {
										$resultFRSC = null;
										}
									
									$FirstRtriveBit=$resultFRSC;
									$MinusSecondRtriveBit=$FirstRtriveBit-1;
									
									
									try{
										if($MinusSecondRtriveBit!='-1')
										{
											$packet1 = new WriteSingleRegisterRequest(366,$MinusSecondRtriveBit,$unitId);
										$connection->connect()->sendAndReceive($packet1);
										 $FirstUpdate="update coutner_record set StartCount2='".$MinusSecondRtriveBit."' where ip_add='".$ip."' and  Type='S'"; 
											$FirstQuery=mysqli_query($dbm,$FirstUpdate);
										}
									}catch(Throwable $exception){}*/
								
							}
						}
					//sleep(1);
					usleep(200000);
					}
				}
				
				$packet = new ReadHoldingRegistersRequest(364, 1, $unitId);
				$Scannerresult = [];
				try 
				{
				$binaryData = $connection->connect()->sendAndReceive($packet);
				$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(364);
				foreach ($response as $address => $word) {
				$Scannerresult[$address] = ['int16' => $word->getUInt16()];
				}
				} catch (Throwable $exception) {
				$ScannerFRresult = null;
				} 
				
				$ScannerTotalCount1=$Scannerresult[364]['int16']; //Scanner Total Count for reset
				
				$packet = new ReadHoldingRegistersRequest(365, 1, $unitId);
				$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
				$resultFRSC1 = [];
				try {
				$binaryData = $connection->connect()->sendAndReceive($packet);
				$log[] = 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1];
				$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(365);
				foreach ($response as $address => $word) {
						$resultFRSC1[$address] = $word->getInt16();
						}
					} catch (Throwable $exception) {
						$resultFRSC1 = null;
						} finally {
					   // $connection->close();
					}
					
				$packetSR = new ReadHoldingRegistersRequest(366, 1, $unitId);
				$log[] = 'Packet to be sent (in hex): ' . $packetSR->toHex();
				$resultSRSC1 = [];
				try {
				$binaryData = $connection->connect()->sendAndReceive($packetSR);
				$log[] = 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1];
				$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(366);
				foreach ($response as $address => $word) {
						$resultSRSC1[$address] = $word->getInt16();
						}
					} catch (Throwable $exception) {
						$resultSRSC1 = null;
						} finally {
					   // $connection->close();
					}
					
				$TotalScannerRetriveCount='';
				$TotalScannerRetriveCount=$resultFRSC1[365]+$resultSRSC1[366];
				
				if($ScannerTotalCount1>=40 && $TotalScannerRetriveCount>=40)
				{
					//echo $ScannerTotalCount1.'=='.$TotalScannerRetriveCount; echo '<br>';
					//exit;
					if($ScannerTotalCount1==$TotalScannerRetriveCount )
					{
						
						try{
						$packet3S = new WriteSingleRegisterRequest(367, 1,$unitId);					
						$binaryDataSR = $connection->connect()->sendAndReceive($packet3S);
						
						//$packet3 = new WriteSingleRegisterRequest(367, 0,$unitId);
						//$binaryData = $connection->connect()->sendAndReceive($packet3);
						$packet = new ReadHoldingRegistersRequest(364, 1, $unitId);
							$Scannerresult = [];
							try 
							{
							$binaryData = $connection->connect()->sendAndReceive($packet);
							$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(364);
							foreach ($response as $address => $word) {
							$Scannerresult[$address] = ['int16' => $word->getUInt16()];
							}
							} catch (Throwable $exception) {
							$ScannerFRresult = null;
							} 
							
							$ScannerTotalCount1=$Scannerresult[364]['int16']; //Scanner Total Count for reset
							
							$packet = new ReadHoldingRegistersRequest(365, 1, $unitId);
							$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
							$resultFRSC1 = [];
							try {
							$binaryData = $connection->connect()->sendAndReceive($packet);
							$log[] = 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1];
							$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(365);
							foreach ($response as $address => $word) {
									$resultFRSC1[$address] = $word->getInt16();
									}
								} catch (Throwable $exception) {
									$resultFRSC1 = null;
									} finally {
								   // $connection->close();
								}
								
							$packetSR = new ReadHoldingRegistersRequest(366, 1, $unitId);
							$log[] = 'Packet to be sent (in hex): ' . $packetSR->toHex();
							$resultSRSC1 = [];
							try {
							$binaryData = $connection->connect()->sendAndReceive($packetSR);
							$log[] = 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1];
							$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(366);
							foreach ($response as $address => $word) {
									$resultSRSC1[$address] = $word->getInt16();
									}
								} catch (Throwable $exception) {
									$resultSRSC1 = null;
									} finally {
								   // $connection->close();
								}
								
							$TotalScannerRetriveCount2='';
							$TotalScannerRetriveCount2=$resultFRSC1[365]+$resultSRSC1[366];
							usleep(400000);
							if($ScannerTotalCount1==0 && $TotalScannerRetriveCount2==0)
							{
								 $FirstUpdate1="update coutner_record set StartCount1='0',StartCount2='0' where ip_add='".$ip."' and  Type='S'";
								$FirstQuery1=mysqli_query($dbm,$FirstUpdate1);
							}
						}catch (Throwable $exception)
						{}
					}
				}
			}
			
			/*LUX UV Start*/
			if($LuxUv[$CO]=='Y' || $LuxUvB[$CO]=='Y')
			{
				
				$countMaLU='';
				$countMLU=244;
				$quantityLU=1;
				$resultLUX=[];
				
				$packet = new ReadHoldingRegistersRequest(244, 1, $unitId);
				try 
				{
					$binaryData = $connection->connect()->sendAndReceive($packet);
					$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(244);
					foreach ($response as $address => $word) {
					$resultLUX[$address] = $word->getUInt16();
					}
				} catch (Throwable $exception) {
					$resultLUX = null;
					} 
					 $countMaLU=$resultLUX[244]; 
					$countMaLU1=$resultLUX[244]['int16']; 
					
					if($countMaLU!=0)
					{
						if($countMaLU!="" && $countMaLU!=0 && $countMaLU!=-1)
						{
							$SelectRetriveBitLU12="select StartCount1 from coutner_record where ip_add='".$ip."' and Type='L'";
								$QueryRetriveLU12=mysqli_query($dbm,$SelectRetriveBitLU12);
								$CheckRetriveBitLU12=mysqli_fetch_assoc($QueryRetriveLU12);
								$LUFRRetrive2=$CheckRetriveBitLU12['StartCount1'];
							
							for($i2=$LUFRRetrive2;$i2<$countMaLU;$i2++)
							{
								try{
								$packet3 = new WriteSingleRegisterRequest(46, 5,$unitId); // 5 write for PLC connected to software 
								$binaryData = $connection->connect()->sendAndReceive($packet3);
								//echo "connected"; echo '<br>';
								$CheckCon=1;
								}catch(Throwable $exception){
									$exception->getTraceAsString();
									$CheckCon=NULL;
									}
								finally{
									//$connection->close();			
								}
								$SelectRetriveBitLU1="select StartCount1 from coutner_record where ip_add='".$ip."' and Type='L'";
								$QueryRetriveLU1=mysqli_query($dbm,$SelectRetriveBitLU1);
								$CheckRetriveBitLU1=mysqli_fetch_assoc($QueryRetriveLU1);
								$LUFRRetrive=$CheckRetriveBitLU1['StartCount1']+1;
								
								$RealDT='';
								
								try{
									$packet1 = new WriteSingleRegisterRequest(245, $LUFRRetrive,$unitId);
									$connection->connect()->sendAndReceive($packet1);
								}catch(Throwable $exception)
								{}
								
								$packet = new ReadHoldingRegistersRequest(406, 22, $unitId);
								$result = [];
								try {
										$binaryData = $connection->connect()->sendAndReceive($packet);
										$log[] = 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1];
										$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(0);
										foreach ($response as $address => $word) {
										$doubleWord = isset($response[$address + 1]) ? $response->getDoubleWordAt($address) : null;
										$result[$address] = $word->getInt16();
									}
									} catch (Throwable $exception) {
										$result = null;
										$i2=$countMaLU;
										
									} finally {
									   // $connection->close();
									}
									$RealDT=$result[0].'-'.$result[1].'-'.$result[2].' '.$result[3].':'.$result[4].':'.$result[5]; 
									$TempD=date('Y',strtotime($RealDT));
									$RealDT=date('Y-m-d H:i:s',strtotime($RealDT));
									$CheckLu='';
									$SelectLU="select Record_Date_Time from photostability_details where chamber_seq='".$chember."' and Record_Date_Time='".$RealDT."' ORDER BY  Record_Date_Time DESC";
									$QueryLU=mysqli_query($dbm,$SelectLU);
									$RowLU=mysqli_fetch_assoc($QueryLU);
									$CheckLu=mysqli_num_rows($QueryLU);
									if($result[0]!='0' && $result[0]!='1970' && $TempD!='1970' && $CheckLu==0)
									{
										if($LuxUv[$CO]=='Y')
										{
											 $InsertLUXUV="insert into photostability_details(Record_Date_Time,UV_Current_LUX,UV_SET_LUX,UV_Total_LUX,UV_remaining_Time,Flourescent_Current_LUX,Flourescent_SET_LUX,Flourescent_Total_LUX,Flourescent_remaining_Time,chamber_seq,lux_uv_type)values('".$RealDT."','".$result[10]."','".$result[11]."','".$result[12]."','".$result[13]."','".$result[6]."','".$result[7]."','".$result[8]."','".$result[9]."','".$chember."','A')"; 
											$LUXUVquery=mysqli_query($dbm,$InsertLUXUV);
											
										}
										
										if($LuxUvB[$CO]=='Y')
										{
											 $InsertLUXUVB="insert into photostability_details(Record_Date_Time,UV_Current_LUX,UV_SET_LUX,UV_Total_LUX,UV_remaining_Time,Flourescent_Current_LUX,Flourescent_SET_LUX,Flourescent_Total_LUX,Flourescent_remaining_Time,chamber_seq,lux_uv_type)values('".$RealDT."','".$result[18]."','".$result[19]."','".$result[20]."','".$result[21]."','".$result[14]."','".$result[15]."','".$result[16]."','".$result[17]."','".$chember."','B')"; 
											$LUXUVquery=mysqli_query($dbm,$InsertLUXUVB); 
											
											
										}
										$FirstUpdateLU1="update coutner_record set StartCount1='".$LUFRRetrive."' where ip_add='".$ip."' and  Type='L'"; 
										$FirstQueryLU1=mysqli_query($dbm,$FirstUpdateLU1);
									}
									elseif($RealDT==$RowLU['Record_Date_Time'])
									{
										try{
										$packet1 = new WriteSingleRegisterRequest(245, 1,$unitId);
										$connection->connect()->sendAndReceive($packet1);
										$FirstUpdateLU1="update coutner_record set StartCount1='1' where ip_add='".$ip."' and  Type='L'"; 
										$FirstQueryLU1=mysqli_query($dbm,$FirstUpdateLU1);
										}catch(Throwable $exception)
										{}
										
									}
									else
									{
										$FirstRtriveBit=$MinusSecondRtriveBit='';
										$packet = new ReadHoldingRegistersRequest(245, 1, $unitId);
										$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
										$resultFRSC = '';
										try {
										$binaryData = $connection->connect()->sendAndReceive($packet);
										$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(245);
										foreach ($response as $address => $word)
											{
												$resultFRSC=$word->getUInt16();
											}
										
											} catch (Throwable $exception) {
												$resultFRSC = null;
												}
											
											$FirstRtriveBit=$resultFRSC;
											$MinusSecondRtriveBit=$FirstRtriveBit-1;
											
											try 
											{
												if($MinusSecondRtriveBit!='-1')
												{
													$packet1 = new WriteSingleRegisterRequest(245,$MinusSecondRtriveBit,$unitId);
													$connection->connect()->sendAndReceive($packet1);
													$FirstUpdate="update coutner_record set StartCount1='".$MinusSecondRtriveBit."' where ip_add='".$ip."' and  Type='L'"; 
													$FirstQuery=mysqli_query($dbm,$FirstUpdate);
												
												}
												
											}
											catch(Throwable $exception){}
											
											break;
									}
									
								}
							
						}
						
						$packet = new ReadHoldingRegistersRequest(244, 1, $unitId); // Read Total Count Of Master Reading 
						try 
						{
							$binaryData = $connection->connect()->sendAndReceive($packet);
							$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(244);
							$resultLUXCount=[];
							foreach ($response as $address => $word)
							{
								$resultLUXCount[$address] = ['int16' => $word->getUInt16()];
							}
						}
						catch (Throwable $exception)
						{
							$resultLUXCount = null;
						} 
					
						$TotalCountLUX=$resultLUXCount[244]['int16']; //Total count of Master Readting for Reset
						
						$packet = new ReadHoldingRegistersRequest(245, 1, $unitId);
						$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
						$resultRetriveLUX = [];
						try {
						$binaryData = $connection->connect()->sendAndReceive($packet);
						$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(245);
						foreach ($response as $address => $word) {
								$resultRetriveLUX[$address] = $word->getInt16();
								}
							} catch (Throwable $exception) {
								$resultRetriveLUX = null;
								} finally {
							   // $connection->close();
							}
						
						$TotalRetriveLUXCount='';
						$TotalRetriveLUXCount=$resultRetriveLUX[245]; 
						
						if($TotalCountLUX==$TotalRetriveLUXCount )
						{
							try{
								$packet3 = new WriteSingleRegisterRequest(248, 1,$unitId);
								$binaryData = $connection->connect()->sendAndReceive($packet3);
								$FirstUpdate1="update coutner_record set StartCount1='0',StartCount2='0' where ip_add='".$ip."' and  Type='L'";
								$FirstQuery1=mysqli_query($dbm,$FirstUpdate1);
							}catch(Throwable $exception)
							{}
						}
					}
			}
			/*LUX UV End*/
			
		}
	}
	}
    $UpdateDT="update datetimetb set rundatetime='".$current_DT."' where datetimeseq=1";
	$QueryDT=mysqli_query($dbm,$UpdateDT);
	

try{
	$packet3 = new WriteSingleRegisterRequest(46, 5,$unitId); // 5 write for PLC connected to software 
	$binaryData = $connection->connect()->sendAndReceive($packet3);
	//echo "connected"; echo '<br>';
	$CheckCon=1;
	}catch(Throwable $exception){
		$exception->getTraceAsString();
		$CheckCon=NULL;
		}
	finally{
		//$connection->close();			
	}

	}// ip reachable
} else {
    //echo "IP $ip is not reachable." . PHP_EOL;
}
$Select="select * from timezone";
	$TimeQuery=mysqli_query($dbm,$Select);
	$RowTim=mysqli_fetch_assoc($TimeQuery);
	date_default_timezone_set($RowTim['timezone']);
	$date=date("Y-m-d H:i:s");
$update_time="update cronjob_response set response_time='".$date."' where cronjob_value='3'";
	$update_query=mysqli_query($dbm,$update_time);

$updatestat="update all_modbus_run set modbus_run_status = '1',date_time='".$date."',ip='".$ser_row1[$CO]."' where id = '1'"; 
$updatestatquery=mysqli_query($dbm,$updatestat);
}//for loop

$ser_row1=[]; $chem_seq=[]; $no_channel1=[]; $chember_name=[]; $chember_id=[]; $hmi_type=[]; $hmi_type=[]; $hmi_ip_address=[]; $LuxUv=[];
}//if loop
$ser_row1=[]; $chem_seq=[]; $no_channel1=[]; $chember_name=[]; $chember_id=[]; $hmi_type=[]; $hmi_type=[]; $hmi_ip_address=[]; $LuxUv=[];
}//function loop

function Modbus_Event($ser_rowIP)
{
	 $tranpip=$ser_rowIP; 
	//session_start();
//declare(strict_types = 1);

// Same as error_reporting(E_ALL);

ini_set("error_reporting", E_ALL);
/* Remove the execution time limit */
//set_time_limit(0);

/* Iteration interval in seconds */
$sleep_time = 40;
require __DIR__ . '/vendor/autoload.php';

// Report all errors except E_NOTICE
error_reporting(E_ALL & ~E_NOTICE);

$dbe=mysqli_connect('localhost','root','Password@123','moresseh_daas');
require_once 'phpmailer/PHPMailerAutoload.php';
// $db=mysqli_connect('localhost','root','Password@123','moresseh_daas');


//$page = $_SERVER['PHP_SELF'];
// while (TRUE)
// {


$port = 502;
$unitId = 1;
Endian::$defaultEndian = 5;
$Select="select * from timezone";
	$TimeQuery=mysqli_query($dbe,$Select);
	$RowTim=mysqli_fetch_assoc($TimeQuery);
	date_default_timezone_set($RowTim['timezone']);
$current_DT=date("d-m-Y h:i:s");
$TimeChange=date("H:i");

/*Array variable initialize */
$ser_row1=[]; $chem_seq=[]; $no_channel1=[]; $chember_name=''; $chember_id=''; $hmi_type=[]; $hmi_type=[]; $hmi_ip_address=[]; $LuxUv=[];
/*Empty variable initialize*/
$ip=$chember=$ch_type='';

/*Company Name*/
$select_cmp="select company_name from  tbl_company";
$query_cmp=mysqli_query($dbe,$select_cmp);
$row_cmp=mysqli_fetch_assoc($query_cmp);
/*End Company Name*/

/* Check which chamber is active */
$CheckCon=NULL;
$select_ip="select hmi_ip_address,hmi_type,report_type,Chamber_cd,Chamber_Name,ip_address,chamber_seq,no_of_temp_channel from  chamber_master where active_flg=1 and ip_address='".$tranpip."' ";
$ip_query=mysqli_query($dbe,$select_ip);
//$ser_row1=$chem_seq=$no_channel1=$chamber_type=$hmi_type=$hmi_ip_addres=$LuxUv=[];
$ser_row=mysqli_fetch_assoc($ip_query);

	$ser_row1=$ser_row['ip_address'];
	$chem_seq=$ser_row['chamber_seq'];
	$no_channel1=$ser_row['no_of_temp_channel'];
	$chember_name=$ser_row['Chamber_Name'];
	$chember_id=$ser_row['Chamber_cd'];
	$chamber_type=$ser_row['report_type'];
	$hmi_type=$ser_row['hmi_type'];
	$hmi_ip_address=$ser_row['hmi_ip_address'];


if(!empty($ser_row1))
{
/*Chamber Chec End*/
//for($CO=0;$CO<=count($ser_row1);$CO++) //Loop Start to received data from PLC using IP address
//{
	if($dbe)
	{
			$ping_ip=$ser_row1;
			$ping_command = "ping -n 1 -w 3000 " . escapeshellarg($ping_ip);

			// Execute the ping command
			exec($ping_command, $ping_output, $ping_result);

			// Check the ping result
			if ($ping_result === 0) 
			{
				// Check the output for unreachable messages
				$output_string = implode(" ", $ping_output);
				
				if (strpos($output_string, 'Destination host unreachable') !== false) 
				{
					//echo "IP $ip is not reachable!!." . PHP_EOL;
				} 
				else 
				{
					if($hmi_type[$CO]=="A") //Check PLC Type
					{
						/*Check Connection*/
						$ip=$ser_row1;
						$chember=$chem_seq;
						$ch_type=$chamber_type;
						$connection = BinaryStreamConnection::getBuilder()
									->setPort($port)
									->setHost($ip)
									->build();
						try{
							//echo "connected"; echo '<br>';
							$CheckCon=1;
							}catch(Throwable $exception){
								$exception->getTraceAsString();
								$CheckCon=NULL;
								}
							/*End Check Connection*/
							
						if($CheckCon==1)
						{
							$RowRetriveMaster=0;
							$SelectRetriveBit="select * from coutner_record where ip_add='".$ip."' and Type='E'";
							$QueryRetrive=mysqli_query($dbe,$SelectRetriveBit);
							$RowRetriveMaster=mysqli_num_rows($QueryRetrive);
							if($RowRetriveMaster==0)
							{
								$InsertCountM="insert into  coutner_record (ip_add,StartCount1,StartCount2,Type,Last_DateTime) values ('".$ip."',0,0,'E','".$current_DT."') "; 
								$QueryCountM=mysqli_query($dbe,$InsertCountM);
							}
							/*Check critical or Normal*/
							$CurrentVal=0;
							$CurrentAdd=356;
							$CurrentQty=1;
							$packet = new ReadHoldingRegistersRequest($CurrentAdd, $CurrentQty, $unitId);
							$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
							$resultCurrent = [];
							try {
								$binaryData = $connection->connect()->sendAndReceive($packet);
								$log[] = 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1];
								$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress($CurrentAdd);
								foreach ($response as $address => $word) {
									$resultCurrent[$address] = $word->getInt16();
									}
								} catch (Throwable $exception) {
									$resultCurrent = null;
								} finally {
								   // $connection->close();
								}
								$CurrentVal=$resultCurrent[356]; 
							
							
							/*End critical or normal*/
							
							
							/*Event Start*/
							$temp_set=$hum_set=$temp_val=$hum_val=$countEv=$LastCountE=$datetimeE='';
							$Mstartingadd=262;
							$Mquantity=4;
							$packet = new ReadHoldingRegistersRequest($Mstartingadd, $Mquantity, $unitId);
							$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
							$result2 = [];
							try {
								$binaryData = $connection->connect()->sendAndReceive($packet);
								$log[] = 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1];
								$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress($Mstartingadd);
								foreach ($response as $address => $word) {
									$result2[$address] = $word->getInt16();
									}
								} catch (Throwable $exception) {
									$result2 = null;
								} finally {
								   // $connection->close();
								}
							$temp_set=number_format((float)($result2[262])/10, 1, '.', '');
							
							$hum_set=number_format((float)($result2[263])/10, 1, '.', '');
							
							$temp_val=number_format((float)($result2[264])/10, 1, '.', '');
							
							$hum_val=number_format((float)($result2[265])/10, 1, '.', '');
							
							$packet = new ReadHoldingRegistersRequest(174, 1, $unitId);
							$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
							$resultE = [];
							try {
								$binaryData = $connection->connect()->sendAndReceive($packet);
								$log[] = 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1];
								// @var $response ReadHoldingRegistersResponse 
								$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(174);
						
								foreach ($response as $address => $word) {
									$resultE[$address] = ['int16' => $word->getUInt16()];
								}
							} catch (Throwable $exception) {
								$resultE = null;
								
							} finally {
								//$connection->close();
							}
							$countEv=$resultE[174]['int16']; 
							$LastCountE=$resultE[174]['int16'];
							/*if($LastCountE==0)
							{
								$FirstUpdate1="update coutner_record set StartCount1='0',StartCount2='0' where ip_add='".$ip."' and  Type='E'";
								$FirstQuery1=mysqli_query($dbe,$FirstUpdate1);
							}*/
							if($countEv!=""&&$countEv!=0)
							{
								
								for($eEvent=0;$eEvent<=$countEv;$eEvent++)
								{ 
									try{
										$packet3 = new WriteSingleRegisterRequest(46, 5,$unitId); // 5 write for PLC connected to software 
										$binaryData = $connection->connect()->sendAndReceive($packet3);
										//echo "connected"; echo '<br>';
										$CheckCon=1;
										}catch(Throwable $exception){
											$exception->getTraceAsString();
											$CheckCon=NULL;
											}
										finally{
											//$connection->close();			
										}
									respons();
									if($TimeChange=="23:55")
									{
										SetDateTime();
									}
									$MasterFRRetrive=0;
									$SelectRetriveBit1="select StartCount1 from coutner_record where ip_add='".$ip."' and Type='E'";
									$QueryRetrive1=mysqli_query($dbe,$SelectRetriveBit1);
									$CheckRetriveBitMaster1=mysqli_fetch_assoc($QueryRetrive1);
									$MasterFRRetrive=$CheckRetriveBitMaster1['StartCount1']+1;
									
								if($MasterFRRetrive<=$LastCountE)
								{
									
										$packet1 = new WriteSingleRegisterRequest(175, $MasterFRRetrive,$unitId); 
										try{
										$connection->connect()->sendAndReceive($packet1);	
										}catch(Throwable $exception)
										{}
										$FirstUpdate="update coutner_record set StartCount1='".$MasterFRRetrive."' where ip_add='".$ip."' and  Type='E'";
												$FirstQuery=mysqli_query($dbe,$FirstUpdate);
										
										$packet = new ReadHoldingRegistersRequest(142,102, $unitId);
										$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
										$result = [];
										try {
											$binaryData = $connection->connect()->sendAndReceive($packet);
											$log[] = 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1];
											$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(142);
											foreach ($response as $address => $word) {
												$result[$address] = $word->getInt16();
												}
											} catch (Throwable $exception) {
												$result = null;
												$eEvent=$countEv;
											} finally {
											   // $connection->close();
											}
											
											$datetimeE=$result[142].'-'.$result[143].'-'.$result[144].' '.$result[145].':'.$result[146].':'.$result[147];
											$datetimeE=date('Y-m-d H:i:s',strtotime($datetimeE)); 
										if($result[142]!=0 && $result[142]!='1970' && $MasterFRRetrive!='-1')
										{
											if($result[168]!="0" || $result[169]!="0")
											{
												$SelectReasonId="select reason_seq from reason where ModbusValue='".$result[168]."' OR ModbusValue='".$result[169]."' "; 
												$QueryReason=mysqli_query($dbe,$SelectReasonId);
												$RowReason=mysqli_fetch_assoc($QueryReason);
												$RowPlcReason='';
												$SelectPlcReason="select * from reasonfromplc where ip='".$ip."'";
												$QueryPlcReason=mysqli_query($dbe,$SelectPlcReason);
												$RowPlcReason=mysqli_num_rows($QueryPlcReason);
												
												if($RowPlcReason=="")
												{
													$InsertPlcReason="insert into reasonfromplc(reason_seq,PlcReasonDate,ip)value('".$RowReason['reason_seq']."','".$datetimeE."','".$ip."')";
													$QueryInsert=mysqli_query($dbe,$InsertPlcReason);
												}
												else
												{
													$UpdatePlcReason="update reasonfromplc set reason_seq='".$RowReason['reason_seq']."' where ip='".$ip."'";
													$QueryUpdatePlcReason=mysqli_query($dbe,$UpdatePlcReason);
												}
											}	
											$ReasonId="";
											if($result['148']=="1024")
											{
												$SelectPlcReasonR="select * from reasonfromplc where ip='".$ip."'";
												$QueryPlcReasonR=mysqli_query($dbe,$SelectPlcReasonR);
												$ReasonIdDate=mysqli_fetch_assoc($QueryPlcReasonR);
												$ReasonId=$ReasonIdDate['reason_seq'];
												$Truncate="delete  from reasonfromplc where ip='".$ip."'";
												$Truncate_query=mysqli_query($dbe,$Truncate);
												
											}
											
											for($p=148;$p<=173;$p++)
											{
												
												$eventValue1=[];
												$ActiveUser='';
												if($result[$p]!=""&&$result[$p]!=0)
												{
													
														$selectE="select * from event where event_value='".$result[$p]."' and register_address='".$p."' ";
														$select_query=mysqli_query($dbe,$selectE);
														$select_fetch=mysqli_fetch_assoc($select_query);
														$temp=$select_fetch['old_new_add'];
														$eventValue1=explode(",",$temp);
														$event_type=$select_fetch['event_type'];
														$event_cd=$select_fetch['event_cd'];
														$event_dc=$select_fetch['event_description'];
														$EventValue=$select_fetch['event_value'];
														$EventRegi=$select_fetch['register_address'];
														$EVCT=0;
														 $SelectEventtime="select * from fired_event_log where chamber_seq='".$chember."' and event_cd ='".$event_cd."' and event_date_time='".$datetimeE."'"; 
														$QueryECT=mysqli_query($dbe,$SelectEventtime);
														 $EVCT=mysqli_num_rows($QueryECT); 
														if($EVCT==0)
														{
															if($event_type=="E")
															{
																
																$chamber_event="select * from chamber_event where chamber_seq='".$chember."' and event_cd ='".$event_cd."'";
																$query_event=mysqli_query($dbe,$chamber_event);
															
																$event_row=mysqli_fetch_assoc($query_event);
																$var1="";
																
																$var1 =unserialize($event_row['user_id']);
																$user_sms=$event_row['sms'];
																
																if($result[172]==0 && $result[171]==0)
																{
																$select_user1= "select user_id from tbl_user where hmi='".$result[173]."' and user_status='1'";
																$select_mo1=mysqli_query($dbe,$select_user1);
																$user_row1=mysqli_fetch_assoc($select_mo1);
																}
																if($result[172]!=0){
																$select_user1= "select user_id from tbl_user where biometric_uid='".$result[172]."' and user_status='1'";
																$select_mo1=mysqli_query($dbe,$select_user1);
																$user_row1=mysqli_fetch_assoc($select_mo1);
																}
																if($result[171]!=0){
																$select_user1= "select user_id from tbl_user where biometric_uid='".$result[171]."' and user_status='1'";
																$select_mo1=mysqli_query($dbe,$select_user1);
																$user_row1=mysqli_fetch_assoc($select_mo1);
																}
																
																 if($result[168]!=4096 && $result[168]!=8192 && $result[169]!=1 && $result[169]!=2 && $result[169]!=4 && $result[169]!=8 && $result[169]!=16 && $result[169]!=16 && $result[169]!=32)
																{ 
																	$insertE="insert into fired_event_log(event_cd,reason_description,fired_event_type,event_date_time,chamber_seq,old_value,new_value,ack_user_id,reason_seq,remarks,event_status,user_id,hmi)value('".$select_fetch['event_cd']."','".$select_fetch['event_description']."','E','".$datetimeE."','".$chember."','','','','".$ReasonId."','','','".$user_row1['user_id']."','".$result[173]."')";  
																	$insert_query=mysqli_query($dbe,$insertE); 
																	/*if($LastCountE!=0)
																	{
																		$FirstUpdate="update coutner_record set StartCount1='".$MasterFRRetrive."' where ip_add='".$ip."' and  Type='E'";
																		$FirstQuery=mysqli_query($dbe,$FirstUpdate);
																	}
																	sleep(1);*/
																}
																else{
																	$SelectLE="select fired_event_log_Seq from fired_event_log ORDER BY fired_event_log_Seq DESC LIMIT 1 ";
																	$QueryLE=mysqli_query($dbe,$SelectLE);
																	$RowLE=mysqli_fetch_assoc($QueryLE);
																	$UpdateEU="update fired_event_log set reason_seq='".$ReasonId."' where fired_event_log_Seq='".$RowLE['fired_event_log_Seq']."'"; 
																	$QueryEU=mysqli_query($dbe,$UpdateEU);
																	mysqli_error($dbe);
																	sleep(1);
																	
																} 
																if($var1!="")
																{
																	for($u=0;$u<count($var1);$u++)
																	{
																		$SelectDate="select event_date_time,hmi from fired_event_log where event_cd ='".$event_cd."' ORDER BY fired_event_log_Seq DESC LIMIT 1 ";
																		$QueryDate=mysqli_query($dbe,$SelectDate);
																		$RowDate=mysqli_fetch_assoc($QueryDate);
																		$current_DTL= date('d-m-Y H:i:s',strtotime($RowDate['event_date_time']));
																		
																		$select_Name= "select username from tbl_user where hmi='".$RowDate['hmi']."' and user_status='1'";
																		$QueryName=mysqli_query($dbe,$select_Name);
																		$user_Name=mysqli_fetch_assoc($QueryName);
																		
																		if($user_Name['username']!="")
																		{
																			$ActiveUser=$user_Name['username'];
																		}else{
																			$ActiveUser="";
																		}
																		 
																		$select_user= "select username,mobile_number,email_address from tbl_user where user_id='".$var1[$u]."' and user_status='1'";
																		$select_mo=mysqli_query($dbe,$select_user);
																		$user_row=mysqli_fetch_assoc($select_mo);
																		$to=$user_row['email_address'];
																		$username=$user_row['username'];
																		$mobile_number=$user_row['mobile_number'];
																		
																		$event_msg="";
																		if(!empty($mobile_number))
																		{
																			if ($ch_type == "T") {
																				$event_msg = "CH:".$chember_name."/ID:".$chember_id."/D&T:".$current_DTL."/E:".$event_dc."/SV:".$temp_set." 0C/PV:".$temp_val." 0C/UserID:".$ActiveUser;
																			} else {
																				$event_msg = "CH:".$chember_name."/ID:".$chember_id."/D&T:".$current_DTL."/E:".$event_dc."/TSV:".$temp_set." 0C/TPV:".$temp_val." 0C/RHSV:".$hum_set."%RH/RHPV:".$hum_val."%RH/UserID:".$ActiveUser;
																			}
																			$insert_msg1="insert into sms_email_log(chamber_seq,user_id,email_id,mobile_no,msg,temp_set,temp_prv,hum_set,hum_prv,event_dc,event_date,date_time,SMSstatus,EmailStatus,ActiveUser)value('".$chember."','".$username."','".$to."','".$mobile_number."','".$event_msg."','".$temp_set."','".$temp_val."','".$hum_set."','".$hum_val."','".$event_dc."','".$current_DTL."','".date('Y-m-d H:i:s')."','1','','".$ActiveUser."')";
																			$msg_query=mysqli_query($dbe,$insert_msg1);
																		}
																		if(!empty($to))
																		{
																			if ($ch_type == "T") {
																				$event_msg = "CH:".$chember_name."/ID:".$chember_id."/D&T:".$current_DTL."/E:".$event_dc."/SV:".$temp_set." 0C/PV:".$temp_val." 0C/UserID:".$ActiveUser;
																			} else {
																				$event_msg = "CH:".$chember_name."/ID:".$chember_id."/D&T:".$current_DTL."/E:".$event_dc."/TSV:".$temp_set." 0C/TPV:".$temp_val." 0C/RHSV:".$hum_set."%RH/RHPV:".$hum_val."%RH/UserID:".$ActiveUser;
																			}
																			$insert_msg1="insert into sms_email_log(chamber_seq,user_id,email_id,mobile_no,msg,temp_set,temp_prv,hum_set,hum_prv,event_dc,event_date,date_time,SMSstatus,EmailStatus,ActiveUser)value('".$chember."','".$username."','".$to."','".$mobile_number."','".$event_msg."','".$temp_set."','".$temp_val."','".$hum_set."','".$hum_val."','".$event_dc."','".$current_DTL."','".date('Y-m-d H:i:s')."','','1','".$ActiveUser."')";
																			$msg_query=mysqli_query($dbe,$insert_msg1);
																		}
																		usleep(500000);
																	}//insert no of user agains the chamber event
																	$var1='';
																}//Check event msg send or not send
																 
									
																/*Email End*/
																
															}// this is only for event analysis process
															if($result[173]!="" && $result[173]!=0)
															{
																if($event_type=="A")
																{
																	if($eventValue1[0]!="")
																	{
																		$chamber_event="select * from chamber_event where chamber_seq='".$chember."' and event_cd ='".$event_cd."'";
																		$query_event=mysqli_query($dbe,$chamber_event);
																		$event_row=mysqli_fetch_assoc($query_event);
																		$var1="";
																		$var1 =unserialize($event_row['user_id']);
																		$user_sms=$event_row['sms'];
																		
																		if($temp=='180,181' || $temp == '182,183' || $temp=='188,189' || $temp == '186,187' || $temp == '190,191' || $temp == '192,193' || $temp == '218,219' || $temp == '220,221' || $temp == '222,223' || $temp == '226,227'|| $temp == '234,235' || $temp == '228,229' || $temp == '230,231' || $temp == '232,233'|| $temp == '242,243' || $temp == '236,237')
																		{	
																					
																			if($temp=='242,243' )
																			{
																				  $new=$result[242].'0';
																				  $old=$result[243].'0';
																			}elseif($temp=='234,235'){
																				  $new=$result[234].'0';
																				  $old=$result[235].'0';
																			}elseif($temp=='236,237'){
																				
																				   $new=$result[236].'0';
																				   $old=$result[237].'0';
																			}
																			else{
																				$new=number_format((float)($result[$eventValue1[0]])/10, 1, '.', '');
																				$old=number_format((float)($result[$eventValue1[1]])/10, 1, '.', '');
																			}
																			
																		}else
																		{
																			$new=number_format((float)($result[$eventValue1[0]]), 1, '.', '');
																			$old=number_format((float)($result[$eventValue1[1]]), 1, '.', '');
																		}
																		
																			$select_user12= "select user_id from tbl_user where hmi='".$result[173]."' and user_status='1'";
																			$select_mo12=mysqli_query($dbe,$select_user12);
																			$user_row12=mysqli_fetch_assoc($select_mo12);
																			
																			$insertON="insert into fired_event_log (chamber_seq,event_date_time,old_value,new_value,fired_event_type,ack_user_id,reason_seq,remarks,event_status,event_cd,user_id,reason_description,hmi)value('".$chember."','".$datetimeE."','".$old."','".$new."','A','','','','','".$select_fetch['event_cd']."','".$user_row12['user_id']."','".$select_fetch['event_description']."','".$result[173]."')"; 
																			$insert_query=mysqli_query($dbe,$insertON);
																			/*if($LastCountE!=0)
																			{
																				$FirstUpdate="update coutner_record set StartCount1='".$MasterFRRetrive."' where ip_add='".$ip."' and  Type='E'";
																				$FirstQuery=mysqli_query($dbe,$FirstUpdate);
																			}*/
																			$SelectDate="select event_date_time,hmi from fired_event_log where event_cd ='".$event_cd."'";
																			$QueryDate=mysqli_query($dbe,$SelectDate);
																			$RowDate=mysqli_fetch_assoc($QueryDate);
																			$current_DTL= date('d-m-Y H:i:s',strtotime($RowDate['event_date_time']));
																			
																			$select_Name= "select username from tbl_user where hmi='".$RowDate['hmi']."' and user_status='1'";
																			$QueryName=mysqli_query($dbe,$select_Name);
																			$user_Name=mysqli_fetch_assoc($QueryName);
																			
																			if($user_Name['username']!="")
																			{
																				$ActiveUser=$user_Name['username'];
																			}else{
																				$ActiveUser="";
																			}
																			if($var1!="")
																			{
																				$SelectDate="select event_date_time,hmi from fired_event_log where event_cd ='".$event_cd."' ORDER BY fired_event_log_Seq DESC LIMIT 1 ";
																				$QueryDate=mysqli_query($dbe,$SelectDate);
																				$RowDate=mysqli_fetch_assoc($QueryDate);
																				$current_DTL= date('d-m-Y H:i:s',strtotime($RowDate['event_date_time']));
																				
																				$select_Name= "select username from tbl_user where hmi='".$RowDate['hmi']."' and user_status='1'";
																				$QueryName=mysqli_query($dbe,$select_Name);
																				$user_Name=mysqli_fetch_assoc($QueryName);
																				
																				if($user_Name['username']!="")
																				{
																					$ActiveUser=$user_Name['username'];
																				}else{
																					$ActiveUser="";
																				}
																				for($u=0;$u<count($var1);$u++)
																				{
																					$select_user= "select username,mobile_number,email_address from tbl_user where user_id='".$var1[$u]."' and user_status='1'";
																					$select_mo=mysqli_query($dbe,$select_user);
																					$user_row=mysqli_fetch_assoc($select_mo);
																					$to=$user_row['email_address'];
																					$username=$user_row['username'];
																					$mobile_number=$user_row['mobile_number'];
																					$event_msg="";
																					if(!empty($mobile_number))
																					{
																						if ($ch_type == "T") {
																							$event_msg = "CH:".$chember_name."/ID:".$chember_id."/D&T:".$current_DTL."/E:".$event_dc."/SV:".$temp_set." 0C/PV:".$temp_val." 0C/UserID:".$ActiveUser;
																						} else {
																							$event_msg = "CH:".$chember_name."/ID:".$chember_id."/D&T:".$current_DTL."/E:".$event_dc."/TSV:".$temp_set." 0C/TPV:".$temp_val." 0C/RHSV:".$hum_set."%RH/RHPV:".$hum_val."%RH/UserID:".$ActiveUser;
																						}
																						$insert_msg1="insert into sms_email_log(chamber_seq,user_id,email_id,mobile_no,msg,temp_set,temp_prv,hum_set,hum_prv,event_dc,event_date,date_time,SMSstatus,EmailStatus,ActiveUser)value('".$chember."','".$username."','".$to."','".$mobile_number."','".$event_msg."','".$temp_set."','".$temp_val."','".$hum_set."','".$hum_val."','".$event_dc."','".$current_DTL."','".date('Y-m-d H:i:s')."','1','','".$ActiveUser."')";
																						$msg_query=mysqli_query($dbe,$insert_msg1);
																					}
																					if(!empty($to))
																					{
																						if ($ch_type == "T") {
																							$event_msg = "CH:".$chember_name."/ID:".$chember_id."/D&T:".$current_DTL."/E:".$event_dc."/SV:".$temp_set." 0C/PV:".$temp_val." 0C/UserID:".$ActiveUser;
																						} else {
																							$event_msg = "CH:".$chember_name."/ID:".$chember_id."/D&T:".$current_DTL."/E:".$event_dc."/TSV:".$temp_set." 0C/TPV:".$temp_val." 0C/RHSV:".$hum_set."%RH/RHPV:".$hum_val."%RH/UserID:".$ActiveUser;
																						}
																						$insert_msg1="insert into sms_email_log(chamber_seq,user_id,email_id,mobile_no,msg,temp_set,temp_prv,hum_set,hum_prv,event_dc,event_date,date_time,SMSstatus,EmailStatus,ActiveUser)value('".$chember."','".$username."','".$to."','".$mobile_number."','".$event_msg."','".$temp_set."','".$temp_val."','".$hum_set."','".$hum_val."','".$event_dc."','".$current_DTL."','".date('Y-m-d H:i:s')."','','1','".$ActiveUser."')";
																						$msg_query=mysqli_query($dbe,$insert_msg1);
																					}
																					usleep(500000);
																				
																			}//insert no of user agains the chamber event
																			$var1='';
																		}//Check chamber event user count
																			 
												
																		/*Email End*/
																		
																	}// check event value is not zero
																}// check eventy is auidt only
															}//check result 173 is not emapty
													}else{
														/*if($LastCountE!=0)
														{
															 $FirstUpdate="update coutner_record set StartCount1='".$MasterFRRetrive."' where ip_add='".$ip."' and  Type='E'";
															$FirstQuery=mysqli_query($dbe,$FirstUpdate);
															
														}*/
													}
													
												}//check result[p] is not empty
												
												
											} //read all plc modbus address
											
											/*if($LastCountE!=0)
											{
												$FirstUpdate="update coutner_record set StartCount1='".$MasterFRRetrive."' where ip_add='".$ip."' and  Type='E'";
												$FirstQuery=mysqli_query($dbe,$FirstUpdate);
												
											}*/
										
										}// check value is not equal to 0 or 1970
																			
									
								} // Check total counter is greater than Counter record counter	 
									
										//echo "success";
										//$connection->close();
								sleep(5);
								$packet = new ReadHoldingRegistersRequest(174, 1, $unitId);
								$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
								$resultE = [];
								try {
									$binaryData = $connection->connect()->sendAndReceive($packet);
									$log[] = 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1];
									// @var $response ReadHoldingRegistersResponse 
									$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(174);
							
									foreach ($response as $address => $word) {
										$resultE[$address] = ['int16' => $word->getUInt16()];
									}
								} catch (Throwable $exception) {
									$resultE = null;
									
								} finally {
									//$connection->close();
								}
								
								$countEv=$resultE[174]['int16'];
								
							}// Event Total Count For loop close 
							$countEv=0;
							
							$packet = new ReadHoldingRegistersRequest(174, 1, $unitId);
							$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
							$resultE = [];
							try {
								$binaryData = $connection->connect()->sendAndReceive($packet);
								$log[] = 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1];
								// @var $response ReadHoldingRegistersResponse 
								$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(174);
						
								foreach ($response as $address => $word) {
									$resultE[$address] = ['int16' => $word->getUInt16()];
								}
							} catch (Throwable $exception) {
								$resultE = null;
								
							} finally {
								//$connection->close();
							}
							
							$LastCountE=$resultE[174]['int16'];
							usleep(100000);
							$packet = new ReadHoldingRegistersRequest(175, 1, $unitId);
							$log[] = 'Packet to be sent (in hex): ' . $packet->toHex();
							$resultERSC = '';
							try {
								$binaryData = $connection->connect()->sendAndReceive($packet);
								$log[] = 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1];
								$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(175);
								foreach ($response as $address => $word)
								{
									$resultERSC=$word->getUInt16();
								}
							
							} catch (Throwable $exception) {
								$resultERSC = null;
								} finally {
							   // $connection->close();
							}
							usleep(400000);
							if($CurrentVal==1)
							{
								if($LastCountE==$resultERSC )
								{
									$packet3 = new WriteSingleRegisterRequest(177, 1,$unitId);
									try{
									$binaryData = $connection->connect()->sendAndReceive($packet3);
									}catch(Throwable $exception)
									{}
									/*$packet3 = new WriteSingleRegisterRequest(177, 0,$unitId);
									try{
									$binaryData = $connection->connect()->sendAndReceive($packet3);
									}catch(Throwable $exception)
									{}*/
									$FirstUpdate1="update coutner_record set StartCount1='0',StartCount2='0' where ip_add='".$ip."' and  Type='E'";
									$FirstQuery1=mysqli_query($dbe,$FirstUpdate1);
								}
								
							}
							else{
							if($resultERSC>=30 && $LastCountE>=30)
							{
								if($LastCountE==$resultERSC )
								{
									$packet3 = new WriteSingleRegisterRequest(177, 1,$unitId);
									try{
									$binaryData = $connection->connect()->sendAndReceive($packet3);
									}catch(Throwable $exception)
									{}
									/*$packet3 = new WriteSingleRegisterRequest(177, 0,$unitId);
									try{
									$binaryData = $connection->connect()->sendAndReceive($packet3);
									}catch(Throwable $exception)
									{}*/
									$FirstUpdate1="update coutner_record set StartCount1='0',StartCount2='0' where ip_add='".$ip."' and  Type='E'";
									$FirstQuery1=mysqli_query($dbe,$FirstUpdate1);
								}
							}
							}
							
								
						}// Count is not emapty
						//sleep(0.80);
					}//CheckCOn check connection
					
				}//Check HMI Type
			} // ip reachable
		}else {
			//echo "IP $ip is not reachable." . PHP_EOL;
		}
	}//Check DataConnection
$Select="select * from timezone";
$TimeQuery=mysqli_query($dbe,$Select);
$RowTim=mysqli_fetch_assoc($TimeQuery);
date_default_timezone_set($RowTim['timezone']);
$date=date("Y-m-d H:i:s");
$updatestat="update all_modbus_run set modbus_run_status = '2',date_time='".$date."',ip='".$ser_row1[$CO]."' where id = '1'"; 
$updatestatquery=mysqli_query($dbe,$updatestat);
	
//}//Total Chamber Count


//$ser_row1=[]; $chem_seq=[]; $no_channel1=[]; $chember_name=[]; $chember_id=[]; $hmi_type=[]; $hmi_type=[]; $hmi_ip_address=[]; $LuxUv=[];
}
}
function SetDateTime()
{


///set_time_limit(0);

/* Iteration interval in seconds */
$sleep_time = 20;

//require_once 'server.php';
$db=mysqli_connect('localhost','root','Password@123','moresseh_daas');
require __DIR__ . '/vendor/autoload.php';
// while(TRUE)
// {
	$type='set_date';	
	$select_ip="select * from chamber_master where active_flg='1'";
	$query_ip=mysqli_query($db,$select_ip);
	$unitId = 1;
	
	$SelectTime="select * from  timezone";
	$Timequery=mysqli_query($db,$SelectTime);
	$RowTime=mysqli_fetch_assoc($Timequery);
	
	date_default_timezone_set($RowTime['timezone']);
	$TimeChange=date("H:i:s");
	$current_DT=date("d-m-Y h:i:s");
	
	
	
	
	if($TimeChange=="23:55:00")
	{
		while($ip=mysqli_fetch_assoc($query_ip))
		{	
			$ip_add=$ip['ip_address']; 
			$chember=$ip['chamber_seq'];
			
			
			$connection = BinaryStreamConnection::getBuilder()
			->setPort(502)
			->setHost($ip_add)
			->build();
			
			/*SV start*/
			global $startAddress;
			
			$packet = new ReadHoldingRegistersRequest(462, 6, $unitId);
			$result3 = [];
			try {
					$binaryData = $connection->connect()->sendAndReceive($packet);
					$log[] = 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1];
					$response = ResponseFactory::parseResponseOrThrow($binaryData)->withStartAddress(462);
					foreach ($response as $address => $word) {
					$doubleWord = isset($response[$address + 1]) ? $response->getDoubleWordAt($address) : null;
					$result3[$address] = $word->getInt16();
				}
				} catch (Exception $exception) {
					$result3 = null;
					
				} finally {
				   // $connection->close();
				}
				
				if($result3[464]!='')
				{
				  $old=$result3[464].'-'.$result3[463].'-'.$result3[462].' '.$result3[465].':'.$result3[466].':'.$result3[467];
			// $old=date('d-m-Y H:i:s',strtotime($current_date_cd));
			
			/*Set_Date_Time*/
			$value=[];
			
			$startAddress=456;
			$value[]=date("Y");
			$value[]=date("m");
			$value[]=date("d");
			$value[]=date("H");
			$value[]=date("i");
			$value[]=date("s");
			
			for($k=0;$k<=5;$k++)
			{
				$packet = new WriteSingleRegisterRequest($startAddress, $value[$k]);
				echo 'Packet to be sent (in hex): ' . $packet->toHex() . PHP_EOL;

				try {
					$binaryData = $connection->connect()
						->sendAndReceive($packet);
					echo 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1] . PHP_EOL;

					/* @var $response WriteSingleRegisterResponse */
					$response = ResponseFactory::parseResponseOrThrow($binaryData);
					echo 'Parsed packet (in hex):     ' . $response->toHex() . PHP_EOL;
					echo 'Register value parsed from packet:' . PHP_EOL;
					print_r($response->getWord()->getInt16());

				} catch (Exception $exception) {
					echo 'An exception occurred' . PHP_EOL;
					echo $exception->getMessage() . PHP_EOL;
					echo $exception->getTraceAsString() . PHP_EOL;
				} finally {
				   //$connection->close();
				}
				$startAddress++;
			}
			$packet = new WriteSingleRegisterRequest(247, 1);
			echo 'Packet to be sent (in hex): ' . $packet->toHex() . PHP_EOL;
			try {
				$binaryData = $connection->connect()
					->sendAndReceive($packet);
				echo 'Binary received (in hex):   ' . unpack('H*', $binaryData)[1] . PHP_EOL;

				/* @var $response WriteSingleRegisterResponse */
				$response = ResponseFactory::parseResponseOrThrow($binaryData);
				echo 'Parsed packet (in hex):     ' . $response->toHex() . PHP_EOL;
				echo 'Register value parsed from packet:' . PHP_EOL;
				print_r($response->getWord()->getInt16());

			} catch (Exception $exception) {
				echo 'An exception occurred' . PHP_EOL;
				echo $exception->getMessage() . PHP_EOL;
				echo $exception->getTraceAsString() . PHP_EOL;
			} finally {
			   //$connection->close();
			}
			$CurrentDatetime=date("Y-m-d H:i:s");
			
			$InserEvent="insert into fired_event_log(chamber_seq,event_date_time,fired_event_type,event_cd,reason_description)values('".$chember."','".$CurrentDatetime."','E','333','Auto Date/Time Synchronize')";	
			$QueryInsert=mysqli_query($db,$InserEvent);
				}
		}
	}
// sleep($sleep_time);	
// }
 /*$updatestat="update all_modbus_run set modbus_run_status = '3',date_time='".$current_DT."' where id = '1'";  
$updatestatquery=mysqli_query($db,$updatestat);*/


}



function respons()
{
	
								
	$reponse="";
	//date_default_timezone_set("Asia/Calcutta");
	//$date=date("Y-m-d H:i:s");
	//require_once 'server.php';
	$db=mysqli_connect('localhost','root','Password@123','moresseh_daas');
	$Select="select * from timezone";
	$TimeQuery=mysqli_query($db,$Select);
	$RowTim=mysqli_fetch_assoc($TimeQuery);
	date_default_timezone_set($RowTim['timezone']);
	$date=date("Y-m-d H:i:s");
	$current_DT=date("d-m-Y h:i:s");
	$reponse=3;
	 $update_time="update cronjob_response set response_time='".$date."' where cronjob_value='".$reponse."'";
	$update_query=mysqli_query($db,$update_time);
	/* $updatestat="update all_modbus_run set modbus_run_status = '0',date_time='".$current_DT."' where id = '1'";  
	$updatestatquery=mysqli_query($db,$updatestat);*/
	SetDateTime();
}
// Release the lock and close the file handle
//flock($fp, LOCK_UN);
//fclose($fp);

// Now, you can safely close the database connection
//mysqli_close($db);
?>
