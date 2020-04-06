<?php


namespace Fragmency\Files\Readers;


use PhpOffice\PhpWord\PhpWord;

trait docx
{
    public function readDocx($file){
        $zip = new \ZipArchive();
        $zip->open($file);
        $index = $zip->locateName("word/document.xml");
        $data = $zip->getFromIndex($index);
        $zip->close();
        $dom = new \DOMDocument();
        $dom->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);

        // set location of docx text content file
        //$xmlFile = $targetDir."/word/document.xml";
        $reader = new \XMLReader;
        $reader->XML($dom->saveXML());

        // set up variables for formatting
        $text = ''; $formatting['bold'] = 'closed'; $formatting['italic'] = 'closed'; $formatting['underline'] = 'closed'; $formatting['header'] = 0;

        // loop through docx xml dom
        while ($reader->read()){
            // look for new paragraphs
            if ($reader->nodeType == \XMLREADER::ELEMENT && $reader->name === 'w:p'){
                // set up new instance of XMLReader for parsing paragraph independantly
                $paragraph = new \XMLReader;
                $p = $reader->readOuterXML();
                $paragraph->xml($p);

                // search for heading
                preg_match('/<w:pStyle w:val="(Heading.*?[1-6])"/',$p,$matches);
                if(isset($matches[1]))
                switch($matches[1]){
                    case 'Heading1': $formatting['header'] = 1; break;
                    case 'Heading2': $formatting['header'] = 2; break;
                    case 'Heading3': $formatting['header'] = 3; break;
                    case 'Heading4': $formatting['header'] = 4; break;
                    case 'Heading5': $formatting['header'] = 5; break;
                    case 'Heading6': $formatting['header'] = 6; break;
                    default:  $formatting['header'] = 0; break;
                }
                else $formatting['header'] = 0;

                // open h-tag or paragraph
                $text .= ($formatting['header'] > 0) ? '<h'.$formatting['header'].'>' : '<p>';

                // loop through paragraph dom
                while ($paragraph->read()){
                    // look for elements
                    if ($paragraph->nodeType == \XMLREADER::ELEMENT && $paragraph->name === 'w:r'){
                        $node = trim($paragraph->readInnerXML());

                        // add <br> tags
                        if (strstr($node,'<w:br ')) $text .= '<br>';

                        // look for formatting tags
                        $formatting['bold'] = (strstr($node,'<w:b/>')) ? (($formatting['bold'] == 'closed') ? 'open' : $formatting['bold']) : (($formatting['bold'] == 'opened') ? 'close' : $formatting['bold']);
                        $formatting['italic'] = (strstr($node,'<w:i/>')) ? (($formatting['italic'] == 'closed') ? 'open' : $formatting['italic']) : (($formatting['italic'] == 'opened') ? 'close' : $formatting['italic']);
                        $formatting['underline'] = (strstr($node,'<w:u ')) ? (($formatting['underline'] == 'closed') ? 'open' : $formatting['underline']) : (($formatting['underline'] == 'opened') ? 'close' : $formatting['underline']);

                        // build text string of doc
                        $text .=     (($formatting['bold'] == 'open') ? '<strong>' : '').
                            (($formatting['italic'] == 'open') ? '<em>' : '').
                            (($formatting['underline'] == 'open') ? '<u>' : '').
                            htmlentities(iconv('UTF-8', 'ASCII//TRANSLIT',$paragraph->expand()->textContent)).
                            (($formatting['underline'] == 'close') ? '</u>' : '').
                            (($formatting['italic'] == 'close') ? '</em>' : '').
                            (($formatting['bold'] == 'close') ? '</strong>' : '');

                        // reset formatting variables
                        foreach ($formatting as $key=>$format){
                            if ($format == 'open') $formatting[$key] = 'opened';
                            if ($format == 'close') $formatting[$key] = 'closed';
                        }
                    }
                }
                $text .= ($formatting['header'] > 0) ? '</h'.$formatting['header'].'>' : '</p>';
            }

        }
        $reader->close();

        // suppress warnings. loadHTML does not require valid HTML but still warns against it...
        // fix invalid html
        //$doc = new \DOMDocument();
        //$doc->encoding = 'UTF-8';
        //@$doc->loadHTML($text);
        //$goodHTML = simplexml_import_dom($doc)->asXML();
        return $text;
    }
}