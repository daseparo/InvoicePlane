<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

/*
 * InvoicePlane
 * 
 * A free and open source web based invoicing system
 *
 * @package		InvoicePlane
 * @author		Kovah (www.kovah.de)
 * @copyright	Copyright (c) 2012 - 2015 InvoicePlane.com
 * @license		https://invoiceplane.com/license.txt
 * @link		https://invoiceplane.com
 * 
 */

class Reports extends Admin_Controller
{

    public function __construct()
    {
        parent::__construct();

        $this->load->model('mdl_reports');
    }

    public function sales_by_client()
    {
        if ($this->input->post('btn_submit')) {
            $data = array(
                'results' => $this->mdl_reports->sales_by_client($this->input->post('from_date'), $this->input->post('to_date'))
            );

            $html = $this->load->view('reports/sales_by_client', $data, true);

            $this->load->helper('mpdf');

            pdf_create($html, trans('sales_by_client'), true);
        }

        $this->layout->buffer('content', 'reports/sales_by_client_index')->render();
    }

    public function payment_history()
    {
        if ($this->input->post('btn_submit')) {
            $data = array(
                'results' => $this->mdl_reports->payment_history($this->input->post('from_date'), $this->input->post('to_date'))
            );

            $html = $this->load->view('reports/payment_history', $data, true);

            $this->load->helper('mpdf');

            pdf_create($html, trans('payment_history'), true);
        }

        $this->layout->buffer('content', 'reports/payment_history_index')->render();
    }

    public function invoice_aging()
    {
        if ($this->input->post('btn_submit')) {
            $data = array(
                'results' => $this->mdl_reports->invoice_aging()
            );

            $html = $this->load->view('reports/invoice_aging', $data, true);

            $this->load->helper('mpdf');

            pdf_create($html, trans('invoice_aging'), true);
        }

        $this->layout->buffer('content', 'reports/invoice_aging_index')->render();
    }

    public function sales_by_year()
    {

        if ($this->input->post('btn_submit')) {
            $data = array(
                'results' => $this->mdl_reports->sales_by_year($this->input->post('from_date'), $this->input->post('to_date'), $this->input->post('minQuantity'), $this->input->post('maxQuantity'), $this->input->post('checkboxTax'))
            );

            $html = $this->load->view('reports/sales_by_year', $data, true);

            $this->load->helper('mpdf');

            pdf_create($html, trans('sales_by_date'), true);
        }

        $this->layout->buffer('content', 'reports/sales_by_year_index')->render();
    }
<<<<<<< HEAD
    //IZVJEŠTAJI RADEŽ
    //
     public function rekapitulacija()
    {

        if ($this->input->post('btn_submit')) {
            $data = array(
                'results' => $this->mdl_reports->rekapitulacija($this->input->post('from_date'), $this->input->post('to_date')),
                'od_datuma' => $this->input->post('from_date'),
                'do_datuma' => $this->input->post('to_date')
            );

            $html = $this->load->view('reports/rekapitulacija', $data, true);

            $this->load->helper('mpdf');
            //peti argument postavljen true da printa broj stranice
            pdf_create($html, trans('rekapitulacija'), true,null,true);
        }

        $this->layout->buffer('content', 'reports/rekapitulacija_index')->render();
    }
 public function dnevni_promet()
    {

        if ($this->input->post('btn_submit')) {
            $data = array(
                'results' => $this->mdl_reports->dnevni_promet($this->input->post('from_date')),
                'od_datuma' => $this->input->post('from_date')

            );

            $html = $this->load->view('reports/dnevni_promet', $data, true);

            $this->load->helper('mpdf');

            pdf_create($html, trans('dnevni_promet'), true,null,true);
        }

        $this->layout->buffer('content', 'reports/dnevni_promet_index')->render();
    }
 public function lista_racuna()
    {

        if ($this->input->post('btn_submit')) {
            $data = array(
                'results' => $this->mdl_reports->lista_racuna($this->input->post('from_date'), $this->input->post('to_date')),
                 'od_datuma' => $this->input->post('from_date'),
                'do_datuma' => $this->input->post('to_date')

            );

            $html = $this->load->view('reports/lista_racuna', $data, true);

            $this->load->helper('mpdf');

            pdf_create($html, trans('lista_racuna'), true,null,true);
        }

        $this->layout->buffer('content', 'reports/lista_racuna_index')->render();
    }




=======
>>>>>>> 84cdfbf65ebf5f859ebe7dfb2bbd6f4bcefda139

}
