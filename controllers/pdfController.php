<?php

class pdfController extends Controller {
    
    private $_pdf;
    public function __construct() 
    {
        parent::__construct();
        $this->getLibrary('fpdf');
        $this->_pdf = new FPDF;
    }
    
    public function index() 
    {
        
    }
    
    public function pdf1($arg) 
    {
        $this->_pdf->AddPage();
        $this->_pdf->SetFont('Arial','B',16);
        $this->_pdf->Cell(40,10, utf8_decode($arg[0] . ' ' . $arg[1]));
        $this->_pdf->Output();
        
    }
    
    public function pdf2($arg)
    {
        require_once ROOT . 'public' . DS . 'files' . DS . 'pdf2.php';
    }
 }
?>

