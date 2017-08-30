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
        foreach($combine_time_array as $combine_time_arrayInd){            
            $date_work = $combine_time_arrayInd[0];
            $arrivalTime = $combine_time_arrayInd[1];
            $leavingTime = $combine_time_arrayInd[3];
            //$a = new DateTime($arrivalTime);
            //$b = new DateTime($leavingTime);
            //$workHours = $a->diff($b);
        }
        print_r($workHours);
        print_r($date_work);
        print_r($combine_time_array);
        die();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $arrayData = array(
            array(NULL, NULL, 'Internal use', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'External use', NULL, NULL, NULL, NULL, NULL, NULL),
            array_fill(0,22,NULL),
            array_fill(0,22,NULL),
            array('Date', 'Arrival time', 'Leaving time', 'No.of hours', 'Normal hours', 'After 21:00', NULL, NULL, 'Extra time from:', NULL, NULL, NULL, 'Date', 'Day' , 'Arrival time', 'Leaving time', 'No.of hours', 'Normal hours', 'After 18:00', NULL, 'Extra time from', NULL),
        );       
        
        $sheet->fromArray(
            $arrayData, // The data to set
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
