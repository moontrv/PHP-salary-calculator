<?php

defined('BASEPATH') OR exit('No direct script access allowed');
$test = "hello";

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
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'xlsx|csv|xls'; //xlsx|csv|xls
        $config['max_size'] = 5000;
        $this->load->library('upload', $config);
    }

    public function index() {
        $this->load->view('header_view');
        $this->load->view('calculate_view');
        $this->load->view('footer_view');
    }

    public function do_upload()
    {
        if ( ! $this->upload->do_upload('userfile'))
        {
            $error = array('error' => $this->upload->display_errors());

            $this->load->view('upload_form', $error);
        }
        else
        {
            $data = array('upload_data' => $this->upload->data());

            $this->load->view('upload_success', $data);
    }
    }
    
    public function readExcel($data) {
        $this->load->library('excel_reader');
        //$data = json_decode(file_get_contents('php://input'), true);
        $data = json_decode(json_encode($data), true);
        
        $config['upload_path'] = './uploads/';
        $config['allowed_types'] = 'xlsx|csv|xls'; //xlsx|csv|xls
        $config['max_size'] = 5000;
        $this->load->library('upload', $config);
        // Read the spreadsheet via a relative path to the document
        // for example $this->excel_reader->read('./uploads/file.xls');
        //$this->excel_reader->read('./uploads/Salary.xlsx');

        // Get the contents of the first worksheet
        /*$worksheet = $this->excel_reader->sheets[0];

        $numRows = $worksheet['numRows']; // ex: 14
        $numCols = $worksheet['numCols']; // ex: 4
        $cells = $worksheet['cells']; // the 1st row are usually the field's name
        foreach ($cells as $cell) {
            //var_dump($cell);
        }*/

        $this->output->set_content_type('application/json');
        return $this->output->set_output(json_encode($data));
        /*$this->load->view('header_view');
        $this->load->view('calculate_view');
        $this->load->view('footer_view');*/
    }   
}
