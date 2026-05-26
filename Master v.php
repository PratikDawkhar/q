<?php
// Decrypted by https://bolt.pawan.krd - Educational Use Only
//
// DISCLAIMER: This file was decrypted for educational and research purposes only.
// Use of this file is at your own risk. We make no warranties, express or implied,
// regarding the accuracy, reliability, or legality of the content within. We are not
// responsible for any damages, direct or indirect, resulting from the use of this file.
// You are solely responsible for ensuring compliance with all applicable laws and
// regulations in your jurisdiction.
//
// By using this file, you acknowledge and agree to these terms.

?> <?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Master extends MY_Controller {
     public function __construct()
    {
        parent::__construct();
        $this->load->helper('pdf');
		$this->session->set_userdata('site_lang',  "chinese");
		$this->session->set_userdata('site_lang',  "english");

    }
	public function index()
	{
	     $remark_details=$url="";
	    if(isset($_POST['btn-submit']))
		{
			$from_date=date('d-m-Y H:i:00',strtotime($_POST['from_date']));
			$to_date=date('d-m-Y H:i:00',strtotime($_POST['to_date']));
			
			$chamber=$_POST['chamber_id'];

	$curdate=strtotime($_POST['from_date']);
            $mydate=strtotime($_POST['to_date']);
$datediff = ($mydate/(60*60*24) - $curdate/(60*60*24));
            $days=abs($datediff);
	 		if ($curdate > $mydate ) {
	                $this->session->set_flashdata('error','Please select From Date smaller than To Date');
				    redirect(base_url('analysis/master'));
				}
			elseif($days>183)
			{
				$this->session->set_flashdata('error','Please select from date and to date between six months period only');
			    redirect(base_url('analysis/master'));

			}
			else
			{
			     if($this->session->userdata('user_permission')=='PREPARED')
			     {
			         $getfooter = $this->common_model->get_records('manage_footer',array('active'),array('value'=>'CHECK'),TRUE);
                    $getactive_flg = $getfooter['active'];
			$url=base_url('analysis/master/generat_pdf/'.$from_date.'/'.$to_date.'/'.$chamber.'/?prepar_by='.$this->session->userdata('user_id').'&vr=&report_seq=&remark_id=&rt=gr');
			if($getactive_flg == 'Y')
        			{
			$insert_array = array(
								'from_date_time	'=>$from_date,
								'to_date_time'=>$to_date,
								'module'=>'Analysis',
								'sub_module'=>'Master Tabular',
								'user_id'=>$this->session->userdata('user_id'),
								'user_permission'=> $this->session->userdata('actual_permission'),
								'assign_to_prepared_by'=>$this->session->userdata('user_id'),
								'assign_date_prepared_by'=>date('Y-m-d H:i:s'),
								'module_url'=>$url,
								'created_date_r' => date('Y-m-d H:i:s'),
								'chamber_seq'=>$chamber,
								);
			$this->common_model->add_records('report_log',$insert_array);
        			}
        			else
        			{
        			    	$insert_array = array(
								'from_date_time	'=>$from_date,
								'to_date_time'=>$to_date,
								'module'=>'Analysis',
								'sub_module'=>'Master Tabular',
								'user_id'=>$this->session->userdata('user_id'),
								'user_permission'=> $this->session->userdata('actual_permission'),
								'assign_to_prepared_by'=>$this->session->userdata('user_id'),
								'assign_date_prepared_by'=>date('Y-m-d H:i:s'),
									'assign_to_checked_by'=>'NA',
    								'assign_date_checked_by'=>'NA',
    								'checkedby_view_report_time'=>'NA',
								'module_url'=>$url,
								'created_date_r' => date('Y-m-d H:i:s'),
								'chamber_seq'=>$chamber,
								);
			$this->common_model->add_records('report_log',$insert_array);
        			}
			}elseif($this->session->userdata('user_permission')=='CHECK'){
			    $url=base_url('analysis/master/generat_pdf/'.$from_date.'/'.$to_date.'/'.$chamber.'/?check_by='.$this->session->userdata('user_id').'&vr=&report_seq=&remark_id=&rt=gr');
			$insert_array = array(
								'from_date_time	'=>$from_date,
								'to_date_time'=>$to_date,
								'module'=>'Analysis',
								'sub_module'=>'Master Tabular',
								'user_id'=>$this->session->userdata('user_id'),
								'user_permission'=> $this->session->userdata('actual_permission'),
								'assign_to_checked_by'=>$this->session->userdata('user_id'),
								'assign_date_checked_by'=>date('Y-m-d H:i:s'),
								'assign_to_prepared_by'=>'NA',
        'assign_date_prepared_by'=>'NA',
								'module_url'=>$url,
								'created_date_r' => date('Y-m-d H:i:s'),
								'chamber_seq'=>$chamber,
								);
			$this->common_model->add_records('report_log',$insert_array);
			   
			}elseif($this->session->userdata('user_permission')=='APPROVED'){
			     $url=base_url('analysis/master/generat_pdf/'.$from_date.'/'.$to_date.'/'.$chamber.'/?approve_by='.$this->session->userdata('user_id').'&vr=&report_seq=&remark_id=&rt=gr');
			$insert_array = array(
								'from_date_time	'=>$from_date,
								'to_date_time'=>$to_date,
								'module'=>'Analysis',
								'sub_module'=>'Master Tabular',
								'user_id'=>$this->session->userdata('user_id'),
								'user_permission'=> $this->session->userdata('actual_permission'),
								'assign_to_approved_by'=>$this->session->userdata('user_id'),
								'assign_date_approved_by'=>date('Y-m-d H:i:s'),
								'module_url'=>$url,
								'created_date_r' => date('Y-m-d H:i:s'),
								'assign_to_prepared_by'=>'NA',
        'assign_date_prepared_by'=>'NA',
				                    'assign_to_checked_by' =>'NA',
				                    'assign_date_checked_by' =>'NA',
				                    'checkedby_view_report_time'=>'NA',
				                    'chamber_seq'=>$chamber,
								);
			$this->common_model->add_records('report_log',$insert_array);
			    
			}else{
			         if($this->session->userdata('user_type')=='ADMIN'){
			             $url=base_url('analysis/master/generat_pdf/'.$from_date.'/'.$to_date.'/'.$chamber.'/?approve_by='.$this->session->userdata('user_id').'&vr=&report_seq=&remark_id=&rt=gr');
			$insert_array = array(
								'from_date_time	'=>$from_date,
								'to_date_time'=>$to_date,
								'module'=>'Analysis',
								'sub_module'=>'Master Tabular',
								'user_id'=>$this->session->userdata('user_id'),
								'user_permission'=> $this->session->userdata('actual_permission'),
								'assign_to_approved_by'=>$this->session->userdata('user_id'),
								'assign_date_approved_by'=>date('Y-m-d H:i:s'),
								'module_url'=>$url,
								'created_date_r' => date('Y-m-d H:i:s'),
									'assign_to_prepared_by'=>'NA',
        'assign_date_prepared_by'=>'NA',
				                    'assign_to_checked_by' =>'NA',
				                    'assign_date_checked_by' =>'NA',
				                    'checkedby_view_report_time'=>'NA',
				                    'chamber_seq'=>$chamber,
								);
			$this->common_model->add_records('report_log',$insert_array);
			             
			         }else{
			             
			             	$urls=base_url('audit/master/generat_pdf/'.$chamber.'/'.$from_date.'/'.$to_date);
			         }
			    
			}
			}

		}
		$this->db->where("active_flg","1");
		$chamber_details=$this->common_model->get_records('chamber_master','');
		$masterreport_details=$this->common_model->get_records('report_type_master','');
		$data=array('middle_content'=>'master-tabular-view','chamber_details'=>$chamber_details,'masterreport_details'=>$masterreport_details,'url'=>$url);	
		$this->load->view('template',$data);
	}
	public function generat_pdf($from_date,$to_date,$chamber)
	{
   $from_date=date('Y-m-d H:i:s',strtotime(str_replace('%20','',$from_date)));
   $to_date=date('Y-m-d H:i:s',strtotime(str_replace('%20','',$to_date)));
 if($_GET['report_seq'] !="" && $_GET['vr'] == "check")
		 {
		  $report_seq = $_GET['report_seq'];
		 $update_array = array(
		                    'checkedby_view_report_time'=>date('Y-m-d H:i:s'),
		                    );
		  $where_array=array('report_log_seq'=>$report_seq);
		 
		  $this->common_model->update_records('report_log',$update_array,$where_array);
		
		 }
		 if($_GET['report_seq'] !="" && $_GET['vr'] == "prepare")
		 {
		  $report_seq = $_GET['report_seq'];
		 $update_array = array(
		                    'preparedby_view_report_time'=>date('Y-m-d H:i:s'),
		                    );
		  $where_array=array('report_log_seq'=>$report_seq);
		 
		  $this->common_model->update_records('report_log',$update_array,$where_array);
		
		 }
		 if($_GET['report_seq'] !="" && $_GET['vr'] == "approve")
		 {
		  $report_seq = $_GET['report_seq'];
		 $update_array = array(
		                    'approvedby_view_report_time'=>date('Y-m-d H:i:s'),
		                    );
		  $where_array=array('report_log_seq'=>$report_seq);
		 
		  $this->common_model->update_records('report_log',$update_array,$where_array);
		
		 }
   
	
				$this->make($from_date,$to_date,$chamber);
	}
/* generate pdf */	
public function make($from_date,$to_date,$chamber)
	{
		
//ini_set('memory_limit', '256M');
//ini_set('max_execution_time', 300); //300 seconds = 5 minutes
$pdf_data=array();
$report_seq = $_GET['report_seq'];
        	$userid = $this->session->userdata('user_id');
$getreport = $this->common_model->get_records('report_log','',array('report_log_seq'=>$report_seq),TRUE);
$getpid = $getreport['pid_remark'];
$getcid = $getreport['cid_remark'];
$getaid = $getreport['aid_remark'];
$getrid = $getreport['rejected_by'];
$getruserdetails = $this->common_model->get_records('tbl_user',array('first_name'),array('user_id'=>$getrid),TRUE);
$getrfirstname = $getruserdetails['first_name'];
$getauserdetails = $this->common_model->get_records('tbl_user',array('first_name'),array('user_id'=>$getaid),TRUE);
$getafirstname = $getauserdetails['first_name'];
$get_userdetails = $this->common_model->get_records('tbl_user',array('first_name'),array('user_id'=>$getpid),TRUE);
$getpfirstname = $get_userdetails['first_name'];
$getcuserdetails = $this->common_model->get_records('tbl_user',array('first_name'),array('user_id'=>$getcid),TRUE);
$getcfirstname = $getcuserdetails['first_name'];
$company_details=$this->common_model->get_records('tbl_company','','',TRUE);
$chamber_details=$this->common_model->get_records('chamber_master','',array('chamber_seq'=>$chamber),TRUE);
$chambername=$chamber_details['Chamber_Name'];
$chambercode=$chamber_details['Chamber_cd'];
$tempbands = $this->common_model->get_records('channel_settings',array('temp_alarm_band','humidity_alarm_band'),array('chamber_seq'=>$chamber),'TRUE');		
 $tempalarmband=$tempbands['temp_alarm_band']==null ? "0.0" :$tempbands['temp_alarm_band'];
 $msHumidityBand=$tempbands['humidity_alarm_band']==null ? "0.0" :$tempbands['humidity_alarm_band'];
 $from_date=date('Y-m-d H:i:s',strtotime($from_date));
 $to_date=date('Y-m-d H:i:s',strtotime($to_date));

  //$last_sql="select  record_date_time  as record_date_time, Temperature_value,temperature_setpoint, humidity_value,humidity_setpoint , DATE_FORMAT( record_date_time , '%d-%m-%y %H:%i:%s') LocalDate  From controller_details Where chamber_seq = '".$chamber."' And record_date_time >='".$from_date."' AND record_date_time <= '".$to_date."' order by record_date_time asc";
$last_sql="select  record_date_time  as record_date_time, Temperature_value,temperature_setpoint, humidity_value,humidity_setpoint , DATE_FORMAT( record_date_time , '%d-%m-%y %H:%i:%s') LocalDate  From controller_details Where chamber_seq = '".$chamber."' And record_date_time >='".$from_date."' AND record_date_time <= '".$to_date."' group by record_date_time order by record_date_time asc";
$data=$this->db->query($last_sql);
$pdf_data=$data->result_array();

$pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor('TMR');
$pdf->SetTitle($this->lang->line('M_O_A'));
$pdf->SetSubject('TMR');
$pdf->SetKeywords('TCPDF, PDF, example, test, guide');
$header_title=ucfirst($company_details['company_name']);
// $pdf->SetFont('kozminproregular', '', 20);

$pdf->SetFont('helvetica', '', 20);
$select="select temperature_setpoint, humidity_setpoint, record_date_time From controller_details Where chamber_seq ='".$chamber."' And record_date_time >='".$from_date."' AND record_date_time <= '".$to_date."' order by record_date_time asc";
 $selectMinMax="select Max(Temperature_value) as maxtempreture, Min(Temperature_value) as mintempreture, Max(humidity_value) as maxhumedity, Min(humidity_value) as minhumedity,ROUND(Avg(Temperature_value),1) as tempavg, ROUND(Avg(humidity_value),1) as humavg From controller_details  Where chamber_seq= '".$chamber."'  And record_date_time >='".$from_date."' AND record_date_time <= '".$to_date."' order by record_date_time asc"; 
$header=$this->db->query($selectMinMax);
$header=$header->row_array();
$temperatureMax=number_format((float)($header['maxtempreture']), 1, '.', '');
$temperatureMin=number_format((float)($header['mintempreture']), 1, '.', '');
$humedityMax=number_format((float)($header['maxhumedity']), 1, '.', '');
$humedityMin=number_format((float)($header['minhumedity']), 1, '.', '');
$tempavg=number_format((float)($header['tempavg']), 1, '.', '');
$humavg=number_format((float)($header['humavg']), 1, '.', '');
$ch = '176';
$fromdate = date('d-m-Y H:i:00',strtotime($from_date));
$todate = date('d-m-Y H:i:00',strtotime($to_date));
$chamber_details_master = $this->common_model->get_records('chamber_master','',array('chamber_seq'=>$chamber),'TRUE');
$chamber_report_type = $chamber_details_master['report_type'];

$selversion = $this->common_model->get_records('sw_version',array('sw_descp'),array('sw_ver'=>'1'),TRUE);
$version = $selversion['sw_descp'];

$getstat = $this->common_model->get_records('historydb','',array('historydb_seq'=>'1'),'TRUE');
$get_detailstat = $getstat['status'];

$master = $this->lang->line('master_tabular_oa');
$date_time_format = $this->lang->line('date_time_format');
$for_period = $this->lang->line('for_the_period');
$to = $this->lang->line('to');
$softver = $this->lang->line('soft_ver');
$historydb = $this->lang->line('note_history_database');
$rejected_label = $this->lang->line('REJECTED_label');
$temp_rel = $this->lang->line('temp_rel');
$temp_degree_relative = $this->lang->line('temp_deg_rel');
$temp_accept = $this->lang->line('temp_accept');
$hum_accept  =$this->lang->line('hum_accept');
$note =$this->lang->line('note');
$temp_relative_time_PSMM = $this->lang->line('temp_deg_rel');
$min_T = $this->lang->line('min_T');
$max_T = $this->lang->line('max_T');
$avg_temp = $this->lang->line('avg_temp');
$min_H = $this->lang->line('min_H');
$max_H = $this->lang->line('max_H');
$avg_H = $this->lang->line('avg_H');
$date_time = $this->lang->line('date/time');
$tmp = $this->lang->line('temprature');
$sv = $this->lang->line('s_v');
$pv = $this->lang->line('pv');
$remark = $this->lang->line('Remark');
$username = $this->lang->line('u_n');
$hum = $this->lang->line('humidity');

if(flagh == 0)
{
$txt=<<<EOD
<hr>
<table border="0" cellpadding="1" cellspacing="1"  >

<tr><td colspan="2"><b>$master: $chambercode - $chambername</b></td></tr>
<tr><td colspan="2"><b>$date_time_format</b></td></tr>
<tr><td colspan="2"><b>$for_period $fromdate $to $todate</b> </td></tr>
<tr><td colspan="2"><b>$softver $version </b> </td></tr><br>
</table>
EOD;
}else
{
    $txt=<<<EOD
<hr>
<table border="0" cellpadding="1" cellspacing="1"  >
<tr><td>$historydb </td></tr>
<tr><td colspan="2"><b>$master: $chambercode - $chambername</b></td></tr>
<tr><td colspan="2"><b>$date_time_format</b></td></tr>
<tr><td colspan="2"><b>$for_period $fromdate $to $todate</b> </td></tr>
<tr><td colspan="2"><b>$softver $version </b> </td></tr><br>
</table>
EOD;
}
$pdf->SetHeaderData('', PDF_HEADER_LOGO_WIDTH,$header_title,$txt);
$pdf->SetFont('helvetica', 'B', 20);
$pdf->setHeaderFont(Array('helvetica', '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(Array('helvetica', '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
$pdf->SetAutoPageBreak(TRUE,28);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	require_once(dirname(__FILE__).'/lang/eng.php');
	$pdf->setLanguageArray($l);
}
$pdf->AddPage();
$pdf->SetFillColor(255, 255, 127);
$pdf->SetFont('helvetica', ' ', 9);
$txt1="";
$get_username = $this->common_model->get_records('report_log','',array('report_log_seq'=>$report_seq),TRUE);
if($get_username['status'] == "REJECTED")
{
$txt1.='
<table border="0" cellpadding="1" cellspacing="1"  >
<tr><td color="red" style="font-size:25px;" align="center"><b>'.$rejected_label.'</b></td></tr>
</table>
';
}
$pdf->writeHTML($txt1, true, false, false, false, '');

if(count($pdf_data)<=0)
{ 
if($chamber_report_type == 'T')
{
$tbl1 = <<<EOF
<style>

th{
	border: 1px solid black;
	font-weight:bold;
	background-color: #C0C0C0;
}
td {
  border: 1px solid black;
}
</style>
<table  border="0" cellpadding="1" cellspacing="1"   >
<tr><td colspan="2"><b>$temp_rel</b></td></tr>
<tr><td><b>$temp_accept:</b>± $tempalarmband </td><td><b>$hum_accept:</b>± $msHumidityBand </td></tr>
<tr><td colspan="2"><b>$note</b></td></tr>
</table>                   
EOF;
$pdf->writeHTML($tbl1,true, false, false, false, '');
}else{
	$tbl1 = <<<EOF
<style>

th{
	border: 1px solid black;
	font-weight:bold;
	background-color: #C0C0C0;
}
td {
  border: 1px solid black;
}
</style>
<table  border="0" cellpadding="1" cellspacing="1"   >
<tr><td colspan="2"><b>$temp_relative_time_PSMM</b></td></tr>
<tr><td><b>$temp_accept:</b>± $tempalarmband </td><td><b>$hum_accept:</b>± $msHumidityBand </td></tr>
<tr><td colspan="2"><b>$note</b></td></tr>
</table>                   
EOF;
$pdf->writeHTML($tbl1,true, false, false, false, '');
}
$pdf->SetFillColor(255, 255, 215);
}else
{
    if($chamber_report_type == 'T')
    {

    $tbl1 = <<<EOF
<style>

th{
	border: 1px solid black;
	font-weight:bold;
	background-color: #C0C0C0;
}
td {
  border: 1px solid black;
}
</style>
<table  border="0" cellpadding="1" cellspacing="1"   >
<tr><td colspan="2"><b>$temp_rel</b></td></tr>
<tr><td colspan="2"><b>$temp_accept:</b>± $tempalarmband </td></tr>
<tr><td colspan="2"><b>$note</b></td></tr>
<tr><td colspan="2"><b>$min_T:</b> $temperatureMin</td></tr>
<tr><td colspan="2"><b>$max_T:</b> $temperatureMax </td> </tr>
<tr><td colspan="2"><b>$avg_temp:</b> $tempavg</td></tr>
</table>                   
EOF;
}
else
{
     $tbl1 = <<<EOF
<style>

th{
	border: 1px solid black;
	font-weight:bold;
	background-color: #C0C0C0;
}
td {
  border: 1px solid black;
}
</style>
<table  border="0" cellpadding="1" cellspacing="1"   >
<tr><td colspan="2"><b>$temp_relative_time_PSMM</b></td></tr>
<tr><td><b>$temp_accept:</b>± $tempalarmband </td><td><b>$hum_accept:</b>± $msHumidityBand </td></tr>
<tr><td colspan="2"><b>$note</b></td></tr>
<tr><td><b>$min_T:</b> $temperatureMin</td> <td><b>$min_H:</b> $humedityMin </td></tr>
<tr><td><b>$max_T:</b> $temperatureMax </td> <td><b>$max_H:</b> $humedityMax </td></tr>
<tr><td><b>$avg_temp:</b> $tempavg</td> <td><b>$avg_H:</b> $humavg </td></tr>
</table>                   
EOF;
}
$pdf->writeHTML($tbl1,true, false, false, false, '');
$pdf->SetFillColor(255, 255, 215);
}

$html="";
if($chamber_report_type == 'T')
{

if(is_array($pdf_data)){
	$k=0;
	$arry=array_chunk($pdf_data, 22);
	$cnt=count($arry);
foreach($pdf_data as $row)
{
  //foreach ($rows as $key => $row) {
	$tempValue=$row['Temperature_value'];
	$humValue=$row['humidity_value'];
	$tempValuemin=$row['temperature_setpoint']-$tempalarmband;
	$tempValuemax=$row['temperature_setpoint']+$tempalarmband;
	$humedityValuemin=$row['humidity_setpoint']-$msHumidityBand;
	$humedityValuemax=$row['humidity_setpoint']+$msHumidityBand;
	if($tempValue < $tempValuemin || $tempValue > $tempValuemax )
	{
		$tempValue=$row['Temperature_value'].'*';
	}else{
		$tempValue=$row['Temperature_value'];
	}
	if($humValue < $humedityValuemin || $humValue > $humedityValuemax )
	{
		$humValue=$row['humidity_value'].'*';
	}else{
		$humValue=$row['humidity_value'];
	}
 $html.='<tr nobr="true" >
 <td align="center">'.date('d-m-Y H:i:00',strtotime($row['record_date_time'])).'</td>
  <td align="center"  >'.$row['temperature_setpoint'].'</td>
 <td align="center"  >'.$tempValue.'</td>
 </tr>';
  //}
$k++;
}
}



$rem="";

 $tbl = <<<EOF
<style>

th{
	border: 1px solid black;
	font-weight:bold;
	background-color: #C0C0C0;
}
td {
  border: 1px solid black;
}
</style>
<table class="first" border="0" cellpadding="5" >
<thead>
 <tr>
  <th align="center" rowspan="2" >$date_time</th>
  <th align="center" colspan="2">$tmp</th>
 </tr>
 <tr>
 <th align="center" >$sv</th>
<th align="center" >$pv</th>
</tr>
</thead>
<tbody>
$html

</tbody>
</table>
EOF;
if( $_GET['remark_id']!="" || $_GET['cremark_id']!="" || $_GET['aremark_id']!="" || $_GET['rejremark_id']!="")
{
	$rem="";
		 $remark_details=$get_username['remark'];
	$rem1="";
		 $cremark_details = $get_username['c_remark'];
 	$rem2="";
 		$aremark_details = $get_username['a_remark'];
	$rem3="";
		$rejremark_details = $get_username['rejected_remark'];
$rem.='
<tr>
<td align="left" colspan="2">'.$remark_details.'</td></tr>
';
$rem1.='
<tr>
<td align="left" colspan="2">'.$cremark_details.'</td></tr>
';
$rem2.='
<tr>
<td align="left" colspan="2">'.$aremark_details.'</td></tr>
';
$rem3.='
<tr>
<td align="left" colspan="2">'.$rejremark_details.'</td></tr>
';

if($get_username['remark'] == '')
{
	$tbl7= <<<EOF
	EOF;
}else{

 
	$tbl7= <<<EOF


<style>

th{
	border-bottom: 1px solid black;
	font-weight:bold;
	background-color: #C0C0C0;
}

table {
	border: 1px solid black;
	border-top: 3px solid black;
}

</style>
<table cellpadding="3" >
<thead>
 <tr>
  <th>$remark</th>
  <th align="right" style="width:50%;">$username - $getpfirstname</th>
 </tr>
</thead>
<tbody>
$rem
</tbody>
</table> 
EOF;
}
if($get_username['c_remark'] == '')
{
	$tbl8= <<<EOF
	EOF;
}else{
$tbl8= <<<EOF
<style>

th{
	border-bottom: 1px solid black;
	font-weight:bold;
	background-color: #C0C0C0;
}

table {
	border: 1px solid black;
	border-top: 3px solid black;
}

</style>
<table cellpadding="3" >
<thead>
 <tr>
  <th>$remark</th>
  <th align="right" style="width:50%;">$username - $getcfirstname</th>
 </tr>
</thead>
<tbody>
$rem1
</tbody>
</table> 
EOF;
}
if($get_username['a_remark'] == '')
{
	$tbl9= <<<EOF
	EOF;
}else{
$tbl9= <<<EOF
<style>

th{
	border-bottom: 1px solid black;
	font-weight:bold;
	background-color: #C0C0C0;
}

table {
	border: 1px solid black;
	border-top: 3px solid black;
}

</style>
<table cellpadding="3" >
<thead>
 <tr>
  <th>$remark</th>
  <th align="right" style="width:50%;">$username - $getafirstname</th>
 </tr>
</thead>
<tbody>
$rem2
</tbody>
</table> 
EOF;}
if($get_username['rejected_remark'] == '')
{
	$tbl10= <<<EOF
EOF;
}else
{
	$tbl10= <<<EOF
<style>

th{
	border-bottom: 1px solid black;
	font-weight:bold;
	background-color: #C0C0C0;
}

table {
	border: 1px solid black;
	border-top: 3px solid black;
}

</style>
<table cellpadding="3" >
<thead>
 <tr>
  <th>$remark</th>
  <th align="right" style="width:50%;">$username - $getrfirstname</th>
 </tr>
</thead>
<tbody>
$rem3
</tbody>
</table> 
EOF;
}
}else{
	$tbl7= <<<EOF
EOF;
$tbl8= <<<EOF
EOF;
$tbl9= <<<EOF
EOF;
$tbl10= <<<EOF
EOF;
}

$pdf->writeHTML($tbl, true, false, false, false, '');
$pdf->lastPage();
$pdf->writeHTML($tbl7, true, false, true, false, '');
$pdf->writeHTML($tbl8, true, false, true, false, '');
$pdf->writeHTML($tbl9, true, false, true, false, '');
$pdf->writeHTML($tbl10, true, false, true, false, '');
$path= FCPATH.'assets/report';
$date=date('d-m-Y H:i:s');
ob_end_clean();
$pdf->Output('Master_Tabular_Offline_Analysis_Report_'.$date.'.pdf', 'I');
}
else
{

if(is_array($pdf_data)){
foreach($pdf_data as $row)
{
	$tempValue=$row['Temperature_value'];
	$humValue=$row['humidity_value'];
	$tempValuemin=$row['temperature_setpoint']-$tempalarmband;
	$tempValuemax=$row['temperature_setpoint']+$tempalarmband;
	$humedityValuemin=$row['humidity_setpoint']-$msHumidityBand;
	$humedityValuemax=$row['humidity_setpoint']+$msHumidityBand;
	if($tempValue < $tempValuemin || $tempValue > $tempValuemax )
	{
		$tempValue=$row['Temperature_value'].'*';
	}else{
		$tempValue=$row['Temperature_value'];
	}
	if($humValue < $humedityValuemin || $humValue > $humedityValuemax )
	{
		$humValue=$row['humidity_value'].'*';
	}else{
		$humValue=$row['humidity_value'];
	}
 $html.='<tr nobr="true">
 <td align="center">'.date('d-m-Y H:i:00',strtotime($row['record_date_time'])).'</td>
 <td align="center"  >'.$row['temperature_setpoint'].'</td>
 <td align="center"  >'.$tempValue.'</td>
 <td align="center"  >'.$row['humidity_setpoint'].'</td>
 <td align="center"  >'.$humValue.'</td> 
 </tr>';
}
}
$tbl4 = <<<EOF
<style>

th{
	border: 1px solid black;
	font-weight:bold;
	background-color: #C0C0C0;
}
td {
  border: 1px solid black;
}
</style>
<table class="first" border="0" cellpadding="5">
<thead>
 <tr>
  <th align="center" rowspan="2" >$date_time</th>
  <th align="center" colspan="2">$tmp</th>
  <th align="center" colspan="2">$hum</th>
 </tr>
 <tr>
 <th align="center" >$sv</th>
<th align="center" >$pv</th>
<th  align="center" >$sv</th>
<th align="center" >$pv</th>
</tr>
</thead>
<tbody>
$html
</tbody>
</table>
EOF; 
if( $_GET['remark_id']!="" || $_GET['cremark_id']!="" || $_GET['aremark_id']!="" || $_GET['rejremark_id']!="")
{
	$rem="";
		 $remark_details=$get_username['remark'];
	$rem1="";
		 $cremark_details = $get_username['c_remark'];
 	$rem2="";
 		$aremark_details = $get_username['a_remark'];
	$rem3="";
		$rejremark_details = $get_username['rejected_remark'];
$rem.='
<tr>
<td align="left" colspan="2">'.$remark_details.'</td></tr>
';
$rem1.='
<tr>
<td align="left" colspan="2">'.$cremark_details.'</td></tr>
';
$rem2.='
<tr>
<td align="left" colspan="2">'.$aremark_details.'</td></tr>
';
$rem3.='
<tr>
<td align="left" colspan="2">'.$rejremark_details.'</td></tr>
';

if($get_username['remark'] == '')
{
	$tbl7= <<<EOF
	EOF;
}else{
	$tbl7= <<<EOF


<style>

th{
	border-bottom: 1px solid black;
	font-weight:bold;
	background-color: #C0C0C0;
}

table {
	border: 1px solid black;
	border-top: 3px solid black;
}

</style>
<table cellpadding="3" >
<thead>
 <tr>
  <th>$remark</th>
  <th align="right" style="width:50%;">$username - $getpfirstname</th>
 </tr>
</thead>
<tbody>
$rem
</tbody>
</table> 
EOF;
}
if($get_username['c_remark'] == '')
{
	$tbl8= <<<EOF
	EOF;
}else{
$tbl8= <<<EOF
<style>

th{
	border-bottom: 1px solid black;
	font-weight:bold;
	background-color: #C0C0C0;
}

table {
	border: 1px solid black;
	border-top: 3px solid black;
}

</style>
<table cellpadding="3" >
<thead>
 <tr>
  <th>$remark</th>
  <th align="right" style="width:50%;">$username - $getcfirstname</th>
 </tr>
</thead>
<tbody>
$rem1
</tbody>
</table> 
EOF;
}
if($get_username['a_remark'] == '')
{
	$tbl9= <<<EOF
	EOF;
}else{
$tbl9= <<<EOF
<style>

th{
	border-bottom: 1px solid black;
	font-weight:bold;
	background-color: #C0C0C0;
}

table {
	border: 1px solid black;
	border-top: 3px solid black;
}

</style>
<table cellpadding="3" >
<thead>
 <tr>
  <th>$remark</th>
  <th align="right" style="width:50%;">$username - $getafirstname</th>
 </tr>
</thead>
<tbody>
$rem2
</tbody>
</table> 
EOF;}
if($get_username['rejected_remark'] == '')
{
	$tbl10= <<<EOF
EOF;
}else
{
	$tbl10= <<<EOF
<style>

th{
	border-bottom: 1px solid black;
	font-weight:bold;
	background-color: #C0C0C0;
}

table {
	border: 1px solid black;
	border-top: 3px solid black;
}

</style>
<table cellpadding="3" >
<thead>
 <tr>
  <th>$remark</th>
  <th align="right" style="width:50%;">$username - $getrfirstname</th>
 </tr>
</thead>
<tbody>
$rem3
</tbody>
</table> 
EOF;
}
}else{
	$tbl7= <<<EOF
EOF;
$tbl8= <<<EOF
EOF;
$tbl9= <<<EOF
EOF;
$tbl10= <<<EOF
EOF;
}

$pdf->writeHTML($tbl4, true, false, false, false, '');
$pdf->lastPage();
$pdf->writeHTML($tbl7, true, false, true, false, '');
$pdf->writeHTML($tbl8, true, false, true, false, '');
$pdf->writeHTML($tbl9, true, false, true, false, '');
$pdf->writeHTML($tbl10, true, false, true, false, '');
$date=date('d-m-Y H:i:s');
$path= FCPATH.'assets/report';
ob_end_clean();
$pdf->Output('Master_Tabular_Offline_Analysis_Report_'.$date.'.pdf', 'I');
}
	}


}

