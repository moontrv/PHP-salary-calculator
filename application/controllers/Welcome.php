<?php

defined('BASEPATH') OR exit('No direct script access allowed');

require 'vendor/autoload.php';     
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Xls;

class Welcome extends CI_Controller {

    /**
     * Index Page for this controller.
     *
     * Maps to the following URL
     * 		http://example.com/index.php/welcome
     * 	- or -
     * 		http://example.com/index.php/welcome/index
     * 	- or -
     * Since this controller is set as the default controller in
     * config/routes.php, it's displayed at http://example.com/
     *
     * So any other public methods not prefixed with an underscore will
     * map to /index.php/welcome/<method_name>
     * @see https://codeigniter.com/user_guide/general/urls.html
     */
    
    public function __construct() {
        parent::__construct();
        $this->load->helper(array('form', 'url'));
        $this->load->library('form_validation');        
    }

    public function index() {
        $this->load->view('header_view'); 
        $this->load->view('calculate_view');
        $this->load->view('footer_view');
    }
    
    public function getFilesAj(){
        $dataToView = array();
        if ($handle = opendir('./uploads/')) {
            while (false !== ($entry = readdir($handle))) {
                if ($entry != "." && $entry != "..") {
                    array_push($dataToView,$entry);
                }
            }
            closedir($handle);
        }
        $this->output->set_content_type('application/json');
        return $this->output->set_output(json_encode($dataToView));
    }

    public function do_upload()
    {
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'xlsx|csv|xls';
        $config['max_size'] = 5000;
        $this->load->library('upload', $config);
        if ( ! $this->upload->do_upload('userfile'))
        {
            $error = array('error' => $this->upload->display_errors());
            print_r($error);
            $this->load->view('header_view'); 
            $this->load->view('calculate_view', $error);
            $this->load->view('footer_view');
        }
        else
        {
            $data = array('upload_data' => $this->upload->data());
            //$this->load->view('upload_success', $data);
            redirect('/Welcome', 'refresh');
    }
    }
    
    public function deleteFile() {
        $data = json_decode(file_get_contents('php://input'), true);
        $data = json_decode(json_encode($data['whole_data']), true);
        
        $this->load->helper("file");        
        $t = './uploads/'.$data;
        unlink($t) or die('failed deleting: ' . $t);
        
        $this->load->view('header_view'); 
        $this->load->view('calculate_view');
        $this->load->view('footer_view');
        
        $this->output->set_content_type('application/json');
        return $this->output->set_output(json_encode("Deleted"));        
    } 
    
    public function readExcel($fileName) {     
        $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
        $spreadsheet = $reader->load('./uploads/'.$fileName);     
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);  
        
        return $sheetData;
    }
    
    public function generateExcel() {
        $data = json_decode(file_get_contents('php://input'), true);
        $data = json_decode(json_encode($data['whole_data']), true);
        
        //Read data from excel to here
        $readFile = $this->readExcel($data);
        //Remove line Barcode,"Question: Status","Timestamp (Scanned)"
        array_shift($readFile);
        
        $calculated_time = array();
        $start_time = array();
        $end_time = array();       
        for ($i = 0; $i <= count($readFile)-1; $i++) { 
            $read = explode(",",$readFile[$i]['A']);
            $date_time = explode(" ",$read[2]);
            //Remove " from corresponding places
            $date_time[0] = substr($date_time[0], 1);
            $date_time[1] = rtrim($date_time[1], '"');
            //$date_time[1] = substr($date_time[0], 0, -1);
            //Separate $date_time to 2 arrays to change array format
            if($i%2==0){
                array_push($start_time, $date_time);
            }else{
                array_push($end_time, $date_time);
            }            
        }
        $combine_time_array = array();
        for ($i = 0; $i <= count($start_time)-1; $i++) {
            array_push($combine_time_array, array_merge($start_time[$i], $end_time[$i]));
        }
        //$combine_time_array has the desired format to be processed
        /*Array
        (
            [0] => Array
                (
                    [0] => "2017-08-18
                    [1] => 11:24:39"
                    [2] => "2017-08-18
                    [3] => 11:48:27"
                )

            [1] => Array
                (
                    [0] => "2017-08-19
                    [1] => 09:00:39"
                    [2] => "2017-08-19
                    [3] => 22:00:27"
                )*/
        $array_to_excel = array();  
        $accumulate_num_hours = 0;
        $accumulate_normal_hours_21 = 0; 
        $accumulate_after_21 = 0;
        foreach($combine_time_array as $combine_time_arrayInd){            
            $date_work = $combine_time_arrayInd[0];
            $date_name = date('D', strtotime($date_work));
            $arrivalTime = $combine_time_arrayInd[1];
            $leavingTime = $combine_time_arrayInd[3];
            $a = DateTime::createFromFormat('H:i:s',$arrivalTime);
            $b = DateTime::createFromFormat('H:i:s',$leavingTime);
            $c = DateTime::createFromFormat('H:i:s', '21:00:00');
            $d = DateTime::createFromFormat('H:i:s', '18:00:00');            
            //Ignore second in calculate time to hours in format example 4.5
            $diff_s = $b->getTimestamp() - $a->getTimestamp();
            $diff_h = round($diff_s/3600,2); 
            $accumulate_num_hours += $diff_h;
            //Calculate overtime after 21
            if($b > $c){
                $diff_s_21 = $b->getTimestamp() - $c->getTimestamp();                
                $diff_h_21 = round($diff_s_21/3600,2); 
                $normal_hours_21 = $diff_h - $diff_h_21;
            }else{
                $diff_h_21 = "0";
                $normal_hours_21 = $diff_h;
            }
            $accumulate_normal_hours_21 += $normal_hours_21;
            $accumulate_after_21 += $diff_h_21;
            //Calculate overtime after 18
            if($b > $d){
                $diff_s_18 = $b->getTimestamp() - $d->getTimestamp();
                $diff_h_18 = round($diff_s_18/3600,2); 
                $normal_hours_18 = $diff_h - $diff_h_18;
            }else{
                $diff_h_18 = "0";
                $normal_hours_18 = $diff_h;
            } 
            //Contruct dynamic part of result excel with all calculation done
            $resultRow = array($date_work, $arrivalTime, $leavingTime, $diff_h, $normal_hours_21, $diff_h_21, NULL, NULL, NULL, NULL, NULL, NULL, $date_work, $date_name, $arrivalTime, $leavingTime, $diff_h, $normal_hours_18, $diff_h_18);   
            array_push($array_to_excel,$resultRow);
        }         
        array_push($array_to_excel,array_fill(0,22,NULL));
        array_push($array_to_excel,array(NULL, NULL, NULL, $accumulate_num_hours, $accumulate_normal_hours_21, $accumulate_after_21, NULL, NULL, NULL, NULL, NULL, NULL, 'Sunday official hour', '7.75', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL));
        array_push($array_to_excel,array(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Sunday after 18:00', '2.75', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL));
        array_push($array_to_excel,array(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Weekday official hour', '17.50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL));
        array_push($array_to_excel,array(NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Weekday day after 18:00', '8.50', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL));
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $arrayData = array(
            array(NULL, NULL, 'Internal use', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'External use', NULL, NULL, NULL, NULL, NULL, NULL),
            array_fill(0,22,NULL),
            array_fill(0,22,NULL),
            array('Date', 'Arrival time', 'Leaving time', 'No.of hours', 'Normal hours', 'After 21:00', NULL, NULL, 'Extra time from:', '21:00', NULL, NULL, 'Date', 'Day' , 'Arrival time', 'Leaving time', 'No.of hours', 'Normal hours', 'After 18:00', NULL, 'Extra time from', '18:00'),
        );       
        $finalArray = array_merge($arrayData,$array_to_excel);
        //print_r($finalArray);

        $sheet->fromArray(
            $finalArray, // The data to set
            NULL,       // Array values with this value will not be set
            'A1'        // Top left coordinate of the worksheet range where we want to set these values (default is A1)
        );
        //$sheet->setCellValue('A4', 'Date', 'B4', 'Arrival time', 'C4', 'Leaving time', 'D4', 'No.of hours','E4', 'Normal hours','F4', 'After 21:00');
        
        $writer = new Xlsx($spreadsheet);
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('./downloads/'.$data);
        
        $this->output->set_content_type('application/json');
        return $this->output->set_output(json_encode($readFile));
    } 
}
