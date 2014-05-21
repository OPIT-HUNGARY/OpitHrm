<?php

/*
 *  This file is part of the {Bundle}.
 * 
 *  (c) Opit Consulting Kft. <info@opit.hu>
 * 
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Opit\Notes\TravelBundle\Manager;

use TCPDF;

/**
 * Description of TravelController
 *
 * @author OPIT Consulting Kft. - PHP Team - {@link http://www.opit.hu}
 * @version 1.0
 * @package Notes
 * @subpackage TravelBundle
 */
class PDFManager
{
    private $marginTop = 15;
    private $marginRight = 15;
    private $marginBottom = 25;
    private $marginLeft = 15;
    
    /**
     * Method to export html to pdf
     * 
     * @param string $content
     * @param string $filename
     * @param string $author
     * @param string $title
     * @param string $subject
     * @param array $keywords
     * @param integer $fontSize
     * @param array $margins
     * @param boolean $setHeader
     * @param boolean $setFooter
     * @param boolean $autoPageBreak
     */
    public function exportToPdf(
        $content,
        $filename,
        $author,
        $title,
        $subject,
        array $keywords,
        $fontSize,
        array $margins,
        $type = 'P',
        $pageSize = 'A4',
        $setHeader = false,
        $setFooter = false,
        $autoPageBreak = true
    ) {
        if (!empty($margins)) {
            $this->setMargins($margins);
        }
        
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor($author);
        $pdf->SetTitle($title);
        $pdf->SetSubject($subject);
        $pdf->SetKeywords($this->setKeywords($keywords));
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins($this->marginLeft, $this->marginTop, $this->marginRight);
        $pdf->SetAutoPageBreak($autoPageBreak, $this->marginBottom);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->SetFont('freeserif', '', $fontSize);
        $pdf->setPrintHeader($setHeader);
        $pdf->setPrintFooter($setFooter);
        $pdf->AddPage($type, $pageSize);
        $pdf->writeHTML($content, true, true, false, '');
        $pdf->lastPage();

        $pdf->Output($filename, 'D');
    }
    
    /**
     * Method to set the margins
     * 
     * @param array $margins
     */
    private function setMargins(array $margins)
    {
        $directions = array('top', 'right', 'bottom', 'left');
        foreach ($directions as $direction) {
            if (array_key_exists($direction, $margins)) {
                $this['margin' . ucfirst($direction)] = $margins[$direction];
            }
        }
    }
    
    /**
     * Method to create string for keywords
     * 
     * @param array $keywords
     * @return string
     */
    private function setKeywords(array $keywords)
    {
        $keywordsString = '';
        foreach ($keywords as $keyword) {
            $keywordsString .= $keyword;
            if ($keyword !== end($keywords)) {
                $keywordsString .= ', ';
            }
        }
        return $keywordsString;
    }
}
