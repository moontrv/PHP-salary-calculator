<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Calculation extends CI_Controller {

    public function readExcel($file_input=null)
    {
        $this->load->library('csvreader');
        $result =   $this->csvreader->parse_file($file_input);

        $data['csvData'] =  $result;
        $this->load->view('header_view');
        $this->load->view('view_csv', $data);
        $this->load->view('footer_view');
    }
}
