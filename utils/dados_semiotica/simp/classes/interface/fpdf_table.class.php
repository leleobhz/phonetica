<?php
//
// SIMP
// Descricao: Extensao da classe FPDF com suporte a impressao de tabelas
// Autor: Rubens Takiguti Ribeiro
// Orgao: TecnoLivre - Cooperativa de Tecnologia e Solucoes Livres
// E-mail: rubens@tecnolivre.com.br
// Versao: 1.0.0.17
// Data: 19/03/2008
// Modificado: 27/01/2010
// Copyright (C) 2008  Rubens Takiguti Ribeiro
// License: LICENSE.TXT
//

// Constants
define('DEBUG_FPDF_TABLE', 0);

class fpdf_table extends fpdf {

    //
    //     Gets page width
    //
    public function GetPageWidth() {
        return $this->w;
    }


    //
    //     Gets page height
    //
    public function GetPageHeight() {
        return $this->h;
    }


    //
    //     Gets margins
    //
    public function GetMargin() {
        return array('t' => $this->tMargin,
                     'r' => $this->rMargin,
                     'b' => $this->bMargin,
                     'l' => $this->lMargin);
    }


    //
    //     Prints a table from a SimpleXMLElement object
    //
    public function PrintTable($t) {
    // SimpleXMLElement || String $t: table in XML format (as SimpleXMLElement or a string)
    //
        $locale = setlocale(LC_NUMERIC, '0');
        setlocale(LC_NUMERIC, 'C');
        $table = $this->CreateTable($t);
        if (!$table) {
            $this->Error('Invalid param type: '.gettype($t));
            return false;
        }

        // Overwrite user unit, page break and bottom margin
        $unit = $this->unit;
        $k  = (double)$this->k;
        $w  = (double)$this->w;
        $h  = (double)$this->h;
        $lMargin = (double)$this->lMargin;
        $tMargin = (double)$this->tMargin;
        $rMargin = (double)$this->rMargin;
        $bMargin = (double)$this->bMargin;
        $cMargin = (double)$this->cMargin;
        $x = (double)$this->x;
        $y = (double)$this->y;
        $LineWidth = (double)$this->LineWidth;
        $FontSize = (double)$this->FontSize;
        $autoPageBreak = $this->AutoPageBreak;
        $TextColor = $this->TextColor;

        $this->unit = 'pt';
        $this->k = 1;
        $this->w = (double)$w * (double)$k;
        $this->h = (double)$h * (double)$k;
        $this->lMargin = (double)$lMargin * (double)$k;
        $this->tMargin = (double)$tMargin * (double)$k;
        $this->rMargin = (double)$rMargin * (double)$k;
        $this->bMargin = (double)$bMargin * (double)$k;
        $this->cMargin = (double)$cMargin * (double)$k;
        $this->x = (double)$x * (double)$k;
        $this->y = (double)$y * (double)$k;
        $this->LineWidth = (double)$LineWidth * (double)$k;
        $this->FontSize = (double)$FontSize * (double)$k;

        $this->SetAutoPageBreak(false);
        $this->bMargin = $this->bMargin ? (double)$this->bMargin : (double)$this->tMargin;
        $this->PageBreakTrigger = $this->h - $this->bMargin;

        // 1 - Alter the SimpleXMLElement updating attributes of each tag
        $this->DefineAttributes($table);

        // 2 - Check which line should be appended to each page and insert
        //     "page_break" attribute on "tr" tags in the end of each page
        $this->PreparePages($table);

        // 3 - Print formated table
        $this->PrintFormatedTable($table);

        // Reset unit, page break and bottom margin
        $this->unit = $unit;
        $this->k  = (double)$k;
        $this->w  = (double)$w;
        $this->h  = (double)$h;
        $this->lMargin = (double)$lMargin;
        $this->tMargin = (double)$tMargin;
        $this->rMargin = (double)$rMargin;
        $this->bMargin = (double)$bMargin;
        $this->cMargin = (double)$cMargin;
        $this->x = (double)$this->x / (double)$k;
        $this->y = (double)$this->y / (double)$k;
        $this->LineWidth = (double)$LineWidth;
        $this->FontSize  = (double)$FontSize;
        $this->SetAutoPageBreak($autoPageBreak, (double)$bMargin);
        $this->TextColor = $TextColor;

        setlocale(LC_NUMERIC, $locale);
    }


    //
    //     Create a SimpleXMLElement object from a string or a SimpleXMLElement $t
    //
    private function CreateTable($t) {
    // SimpleXMLElement || String $t: table in XML format (as SimpleXMLElement or a string)
    //
        // Get XML as string
        if (is_string($t)) {
            $s = $t;
        } elseif (is_object($t)) {
            if (get_class($t) == 'SimpleXMLElement')  {
                $s = $t->asXML();
            }
        } else {
            $this->Error('Invalid type "'.gettype($t).'"');
            return false;
        }

        // Remove Comments
        while ($pos = strpos($s, '<!--')) {
            $pos2 = strpos($s, '-->', $pos);
            $s = substr($s, 0, $pos).substr($s, $pos2 + 3);
        }

        // Create and return the SimpleXMLElement
        $obj = new SimpleXMLElement($s);
        return $obj;
    }


    //
    //     Define attributes of each tag (STEP 1)
    //
    private function DefineAttributes(&$table) {
    // SimpleXMLElement $table: table to be updated
    //
        $this->DefineTableAttributes($table);
        $this->DefineCellAttributes($table);
        $this->DefineWidth($table);
        $this->DefineHeight($table);
    }


    //
    //     Define table attributes
    //
    private function DefineTableAttributes(&$table) {
    // SimpleXMLElement $table: table to be updated
    //
        $default = array('align'       => 'left',
                         'border'      => '0',
                         'bordercolor' => '#000000',
                         'color'       => '#000000',
                         'bgcolor'     => '#FFFFFF'
                         );
        foreach ($default as $attribute => $value) {
            if (!isset($table[$attribute])) {
                $table->addAttribute($attribute, $value);
            }
        }
        list($border, $unit) = sscanf($table['border'], '%d%s');
        $border = (double)$border / (double)$this->getK($unit);
        $table['border'] = sprintf('%0.40f', (double)$border);

        if (isset($table['marginleft'])) {
            list($margin, $unit) = sscanf($table['marginleft'], '%d%s');
            $margin = (double)$margin / (double)$this->getK($unit);
            $table['marginleft'] = sprintf('%0.40f', (double)$margin);
        } elseif (isset($table['marginright'])) {
            list($margin, $unit) = sscanf($table['marginright'], '%d%s');
            $margin = (double)$margin / (double)$this->getK($unit);
            $table['marginright'] = sprintf('%0.40f', (double)$margin);
        }
    }


    //
    //     Define TD tag attributes
    //
    private function DefineTdAttributes(&$table, &$td) {
    // SimpleXMLElement $table: table to use default values
    // SimpleXMLElement $td: td to be updated
    //
        $default = array('align'       => 'left',
                         'bgcolor'     => $table['bgcolor'],
                         'bgimage'     => '',
                         'bgposition'  => 'tl',
                         'bold'        => '0',
                         'border'      => $table['border'],
                         'bordercolor' => $table['bordercolor'],
                         'color'       => $table['color'],
                         'colspan'     => '1',
                         'padding'     => '2px',
                         'rowspan'     => '1',
                         'valign'      => 'top'
                         );
        foreach ($default as $attribute => $value) {
            if (!isset($td[$attribute])) {
                $td->addAttribute($attribute, $value);
            }
        }

        if (!isset($td['fontsize'])) {
            $td->addAttribute('fontsize', $this->FontSize);
        }

        list($border, $unit) = sscanf($td['border'], '%d%s');
        $border = (double)$border / (double)$this->getK($unit);
        $td['border'] = sprintf('%0.40f', (double)$border);

        list($padding, $unit) = sscanf($td['padding'], '%d%s');
        $padding = (double)$padding / (double)$this->getK($unit);
        $td['padding'] = sprintf('%0.40f', (double)$padding);
    }


    //
    //     Define TH tag attributes
    //
    private function DefineThAttributes(&$table, &$th) {
    // SimpleXMLElement $table: table to use default values
    // SimpleXMLElement $th: th to be updated
    //
        $default = array('align'       => 'center',
                         'bgcolor'     => $table['bgcolor'],
                         'bgimage'     => '',
                         'bgposition'  => 'tl',
                         'bold'        => '1',
                         'border'      => $table['border'],
                         'bordercolor' => $table['bordercolor'],
                         'color'       => $table['color'],
                         'colspan'     => '1',
                         'padding'     => '3px',
                         'rowspan'     => '1',
                         'valign'      => 'middle'
                         );
        foreach ($default as $attribute => $value) {
            if (!isset($th[$attribute])) {
                $th->addAttribute($attribute, $value);
            }
        }

        if (!isset($td['fontsize'])) {
            $th->addAttribute('fontsize', $this->FontSize);
        }

        list($border, $unit) = sscanf($th['border'], '%d%s');
        $border = (double)$border / (double)$this->getK($unit);
        $th['border'] = sprintf('%0.40f', (double)$border);

        list($padding, $unit) = sscanf($th['padding'], '%d%s');
        $padding = (double)$padding / (double)$this->getK($unit);
        $th['padding'] = sprintf('%0.40f', (double)$padding);
    }


    //
    //     Define table width, margin left and set de width of each cell
    //
    private function DefineWidth(&$table) {
    // SimpleXMLElement $table: table to be updated
    //
        if (!isset($table->colgroup)) {
            $this->Error('Colgroup was not specified');
        }

        // Get Max Table Width
        $max_width = (double)$this->w - ((double)$this->lMargin + (double)$this->rMargin + (double)2 * (double)$table['border']);
        $table->addAttribute('max_width', sprintf('%0.40f', (double)$max_width));

        // Get Table Width
        $table_width = (double)0;
        $table_col_width = array();
        $total_percent = (double)0;
        foreach ($table->colgroup->col as $col) {
            if (!isset($col['width'])) {
                $this->Error('Column width was not specified');
            }
            list($col_width, $unit) = sscanf($col['width'], "%d%s");
            switch ($unit) {
            case '%':
                $total_percent += (double)$col_width;
                $w = (double)$table['max_width'] * (double)$col_width / (double)100;
                $table_width += (double)$w;
                $table_col_width[] = (double)$w;
                break;
            default:
                $w = (double)$col_width / (double)$this->getK($unit);
                $table_width += (double)$w;
                $table_col_width[] = (double)$w;
                break;
            }
        }
        if (isset($table['width'])) {
            $table['width'] = sprintf('%0.40f', (double)$table_width);
        } else {
            $table->addAttribute('width', sprintf('%0.40f', (double)$table_width));
        }

        // Adjust lost width
        if ($total_percent) {
            $real_width = (double)$total_percent * (double)$max_width / (double)100;
            $dif = (double)$real_width - (double)$table_width;
            $table_col_width[count($table_col_width) - 1] += (double)$dif;
        }

        // We don't need colgroup anymore
        unset($table->colgroup);

        // If margin was set
        if (isset($table['marginleft'])) {
            $margin_left = (double)$table['marginleft'] + (double)$table['border'];
        } elseif (isset($table['marginright'])) {
            $margin_left = (double)$this->w - ((double)$table['marginright'] + (double)$table['width'] + (double)$table['border']);
        } else {

            // Set x0 of table
            switch (strtolower($table['align'])) {
            case 'left':
                $margin_left = (double)$this->lMargin + (double)$table['border'];
                break;
            case 'right':
                $margin_left = (double)$this->w - (double)$table['width'] - (double)$this->rMargin - (double)$table['border'];
                break;
            case 'center':
                $margin_left = (double)$this->lMargin + (((double)$this->w - ((double)$this->lMargin + (double)$this->rMargin)) - (double)$table['width']) / (double)2;
                break;
            }
        }
        $table->addAttribute('x0', sprintf('%0.40f', (double)$margin_left));

        // Set margin_top of table
        if ((double)$table['border']) {
            $margin_top = (double)$table['border'];
        } else {
            $margin_top = 0;
        }
        $table->addAttribute('margin_top', sprintf('%0.40f', (double)$margin_top));

        // Set width of each cell in pt
        foreach ($table->tr as $tr) {
            $column = 0;
            foreach ($tr->children() as $cell) {
                $width = (double)array_sum(array_slice($table_col_width, $column, $cell['colspan']));
                $content_width = (double)$width - ((double)2 * ((double)$cell['padding'] + (double)$cell['border']));
                $cell->addAttribute('width', sprintf('%0.40f', (double)$width));
                $cell->addAttribute('content_width', sprintf('%0.40f', (double)$content_width));
                $column += (int)$cell['colspan'];
            }
        }
    }


    //
    //     Set the height of each table cell
    //
    private function DefineHeight(&$table) {
    // SimpleXMLElement $table: table to be updated
    //
        $check_after = array(); // Cells to be checked after the main loop
        $l = 0; // Current line

        foreach ($table->tr as $tr) {
            $line_height = (double)0;
            $c = 0;
            $child = 0;
            foreach ($tr->children() as $cell) {
                $height = (double)$this->GetCellHeight($cell);

                // Set height of table cell
                $cell['height'] = (double)max((double)$height, (double)$cell['height']);
                $cell['content_height'] = (double)$height - ((double)2 * ((double)$cell['padding'] + (double)$cell['border']));

                // Check rowspan after this loop
                if ((int)$cell['rowspan'] > 1) {
                    $obj = new stdClass();
                    $obj->tag   = $cell->getName();
                    $obj->line  = $l;
                    $obj->child = $child;
                    $check_after[] = $obj;
                } else {
                    $line_height = (double)max((double)$cell['height'], (double)$line_height);
                }
                $c += (int)$cell['colspan'];
                $child++;
            }

            $tr->addAttribute('height', sprintf('%0.40f', (double)$line_height));
            $table_line_height[] = (double)$line_height;
            $l += 1;

            // Update cell Height
            foreach ($tr->children() as $cell) {
                if ((int)$cell['rowspan'] == 1) {
                    $cell['height'] = (double)$line_height;
                }
            }
        }

        // Adjust height of rowspan cells
        foreach ($check_after as $obj) {
            $children = $table->tr[$obj->line]->children();
            $cell = &$children[$obj->child];
            $cell['height'] = (double)array_sum(array_slice($table_line_height, $obj->line, (int)$cell['rowspan']));
        }

        // Adjust lines taller than the page height
        $this->AdjustLongLines($table);
    }


    //
    //     Adjust lines taller than the page height
    //
    private function AdjustLongLines(&$table) {
    // SimpleXMLElement $table: table to be updated
    //
        if (!extension_loaded('dom')) {
            trigger_error('fpdf_table works better with DOM extension.', E_USER_NOTICE);
            return false;

//TODO: implementar uma forma de fazer isso sem a extensao DOM
        }

        $page_height = (double)$this->h - ((double)$this->bMargin + (double)$this->tMargin);
        $lines = count($table->tr);
        $adjust_again = false;
        for ($l = 0; $l < $lines; $l++) {
            $tr = $table->tr[$l];
            if ((double)$tr['height'] > $page_height) {
                $this->SplitLine($table, $l);
                $adjust_again = true;
                break;
            }
        }
        if ($adjust_again) {
            $this->AdjustLongLines($table);
        }
    }


    //
    //     Splits the line
    //
    private function SplitLine(&$table, $line) {
    // SimpleXMLElement $table: table to be updated
    // Int $line: line to be splited
    //
        $page_height = (double)$this->h - ((double)$this->bMargin + (double)$this->tMargin);

        $dom = new DOMDocument();
        $dom->loadXML($table->asXML());
        $tr = &$dom->getElementsByTagName('tr')->item($line);
        $tr_before = $tr->cloneNode(true);
        $count_cells = $tr->getElementsByTagName('*')->length;

        $max_height        = 0.0;
        $max_height_before = 0.0;

        for ($c = 0; $c < $count_cells; $c++) {

            $cell_before = $tr_before->getElementsByTagName('*')->item($c);
            $cell = $tr->getElementsByTagName('*')->item($c);

            // Get text
            $text = $cell->textContent;
            $len = strlen($text);

            // Split text
            $this->SplitCell(simplexml_import_dom($cell), $part1, $part2);

            // Update cell before
            $cell_before->removeAttribute('rowspan');
            $cell_before->firstChild->deleteData(0, $len);
            $cell_before->firstChild->appendData($part1->text);
            $cell_before->setAttribute('content_height', sprintf('%0.40f', (double)$part1->content_height));

            $max_height_before = (double)max((double)$part1->height, (double)$max_height_before);

            // Update Cell
            $cell->firstChild->deleteData(0, $len);
            $cell->firstChild->appendData($part2->text);
            $cell->setAttribute('content_height', sprintf('%0.40f', (double)$part2->content_height));

            $max_height = (double)max((double)$part2->height, (double)$max_height);
        }

        $tr_before->setAttribute('height', sprintf('%0.40f', (double)$max_height_before));
        foreach ($tr_before->getElementsByTagName('*') as $cell) {
            $cell->setAttribute('height', sprintf('%0.40f', (double)$max_height_before));
        }

        $tr->setAttribute('height', sprintf('%0.40f', $max_height));
        foreach ($tr->getElementsByTagName('*') as $cell) {
            $cell->setAttribute('height', sprintf('%0.40f', (double)$max_height));
        }

        $dom->documentElement->insertBefore($tr_before, $tr);
        $table = simplexml_load_string($dom->saveXML());
    }


    //
    //     Splits cell text in 2 parts
    //
    private function SplitCell($cell, &$part1, &$part2) {
    // SimpleXMLElement $cell: cell to be splited
    // stdClass $part1: first slice (text, height and content_height)
    // stdClass $part2: second slice (text, height enad content_height)
    //
        $part1 = new stdClass();
        $part1->text = '';
        $part1->height = 0.0;
        $part1->content_height = 0.0;

        $part2 = new stdClass();
        $part2->text = '';
        $part2->height = 0.0;
        $part2->content_height = 0.0;

        $page_height = (double)$this->h - ((double)$this->bMargin + (double)$this->tMargin);

        $text = (string)$cell;
        $vt_split_pos = $this->GetSplitPos($text);
        for ($i = count($vt_split_pos) - 1; $i >= 0; $i--) {
            $pos = $vt_split_pos[$i];
            $part1->text = substr($text, 0, $pos);
            $part2->text = substr($text, $pos + 1);
            if (empty($part2->text)) {
                $part2->text = ' ';
            }

            $width = (double)$cell['content_width'];
            $border = (double)$cell['border'];
            $padding = (double)$cell['padding'];
            $bold = $cell['bold'];
            $fontsize = (double)$cell['fontsize'];

            $sub = (double)(2 * ((double)$border + (double)$padding));

            $part1->height = (double)$this->GetTextCellHeight($part1->text, $width, $border, $padding, $bold, $fontsize);
            $part1->content_height = (double)$part1->height - (double)$sub;

            if ((double)$part1->height < (double)$page_height) {
                $part2->height = (double)$this->GetTextCellHeight($part2->text, $width, $border, $padding, $bold, $fontsize);
                $part2->content_height = (double)$part2->height - (double)$sub;
                return;
            }
        }
        $part2->height = (double)$this->GetTextCellHeight($part2->text, $width, $border, $padding, $bold, $fontsize);
        $part2->content_height = (double)$part2->height - (double)$sub;
    }


    //
    //     Returns an array of positions of spaces and new lines
    //
    private function GetSplitPos($text) {
    // String $text: text to be parsed
    //
        $pos = array();
        $length = strlen($text);
        for ($i = 0; $i < $length; $i++) {
            switch ($text[$i]) {
            case ' ':
            case "\r":
            case "\n":
            case "\t":
                $pos[] = $i;
            }
        }
        $pos[] = $length;
        return $pos;
    }


    //
    //     Calculates height of text cell
    //
    private function GetTextCellHeight($text, $width, $border, $padding, $bold, $size) {
    // String $text: Text to be checked
    // Float $width: cell width
    // Float $border: border size
    // Float $padding: padding size
    // Bool $bold: bold font
    // Int $size: font size
    //
        $text = utf8_decode($text);
        $text = preg_replace('/<br[\s]*\/>/i', "\n", $text);

        // Get the size of Cell
        $w = (double)$width;
        $h = (double)$size;

        if ($bold) {
            $this->SetFont('', 'B', $h);
        } else {
            $this->SetFont('', '', $h);
        }
        $cell_height = (double)$border +
                       (double)$padding +
                       (double)$this->MultiCellHeight((double)$w, (double)$h, $text) +
                       (double)$padding +
                       (double)$border;

        return $cell_height;
    }


    //
    //     Calculates height of cell
    //
    private function GetCellHeight(&$cell) {
    // SimpleXMLElement $cell: cell to be checked
    //
        // Cell has Text or Image?
        if (!isset($cell->img)) {
            $text = (string)$cell;
            if (preg_match('/.+/u', $text)) {
                $text = utf8_decode($text);
            }
            $text = preg_replace('/<br[\s]*\/>/i', "\n", $text);
        } else {
            list($img_width,  $unit) = sscanf($cell->img['width'],  '%d%s');
            list($img_height, $unit) = sscanf($cell->img['height'], '%d%s');
            $width = (double)$img_width  / (double)$this->getK($unit);
            $height = (double)$img_height / (double)$this->getK($unit);
            $cell->img['width']  = sprintf('%0.40f', (double)$width);
            $cell->img['height'] = sprintf('%0.40f', (double)$height);
        }

        // Get height of text cell
        if (!isset($cell->img)) {

            // Get the size of Cell
            $w = (double)$cell['content_width'];
            $h = (double)$this->FontSize;

            $this->setFont('', $cell['bold'] ? 'B' : '', (double)$cell['fontsize']);
            $cell_height = (double)$cell['border'] +
                           (double)$cell['padding'] +
                           (double)$this->MultiCellHeight((double)$w, (double)$h, $text) +
                           (double)$cell['padding'] +
                           (double)$cell['border'];

        // Get height of image cell
        } else {
            $cell_height = (double)$cell['border'] +
                           (double)$cell['padding'] +
                           (double)$cell->img['height'] +
                           (double)$cell['padding'] +
                           (double)$cell['border'];
        }

        // Convert cell height
        if ((double)$cell['height'] > 0) {
            list($height, $unit) = sscanf($cell['height'], '%d%s');
            $height = (double)$height / (double)$this->getK($unit);
            $cell['height'] = sprintf('%0.40f', (double)$height);
        }

        return $cell_height;
    }


    //
    //     Define attributes of table cells
    //
    private function DefineCellAttributes(&$table) {
    // SimpleXMLElement $table: table to be updated
    //
        foreach ($table->tr as $tr) {
            foreach ($tr->children() as $cell) {
                switch ($cell->getName()) {
                case 'td': $this->DefineTdAttributes($table, $cell); break;
                case 'th': $this->DefineThAttributes($table, $cell); break;
                }
            }
        }
    }


    //
    //     Convert HTML RGB color to Array with 'r', 'g' and 'b' index
    //
    private static function GetHTMLColor($rgb_color) {
    // String $rgb_color: color in RGB HTML format (#XXX or #XXXXXX)
    //
        switch (strlen($rgb_color)) {
        case 4: // Ex: #FFF
            sscanf($rgb_color, '#%X%X%X', $r, $g, $b);
            break;
        case 7: // Ex: #FFFFFF
            sscanf($rgb_color, '#%2X%2X%2X', $r, $g, $b);
            break;
        default:
            $r = $g = $b = 0; // #000000
            break;
        }
        return array('r' => $r,
                     'g' => $g,
                     'b' => $b);
    }


    //
    //     Check which line shoud be appended to each page (STEP 2)
    //
    private function PreparePages(&$table) {
    // SimpleXMLElement $table: table to be updated
    //
        $max_y = (double)$this->h - ((double)$this->bMargin + (double)2 * (double)$table['border']);
        $current_y = (double)$this->y;
        $line = 0;
        foreach ($table->tr as $tr) {
            $can_append = false;

            if (!isset($tr['join'])) {
                $can_append = (double)$current_y + (double)$tr['height'] <= (double)$max_y;
            } else {
                $join_height = (double)0;
                $line_join = $line + (int)$tr['join'];
                for ($i = $line; $i < $line_join; $i++) {
                    $join_height += (double)$table->tr[$i]['height'];
                }
                $can_append = (double)$current_y + (double)$join_height <= (double)$max_y;
            }

            // If the line can be appended to the current page
            if ($can_append) {

                // Check line cells with rowspan
                $max_height = (double)$tr['height'];
                $has_rowspan = false;
                foreach ($tr->children() as $child) {
                    if ((int)$child['rowspan'] > 1 && (double)$child['height'] > (double)$max_height) {
                        $max_height = (double)$child['height'];
                        $has_rowspan = true;
                    }
                }

                // If line has any cell with rowspan
                if ($has_rowspan) {
                    if ((double)$current_y + (double)$max_height <= (double)$max_y) {
                        $current_y += (double)$tr['height'];
                    } else {
                        $current_y = (double)$this->tMargin + (double)$tr['height'];
                        $tr->addAttribute('page_break', 1);
                    }

                } else {
                    $current_y += (double)$tr['height'];
                }

            // If needs to add a page break or split the line
            } else {
                $current_y = (double)$this->tMargin + (double)$tr['height'];
                $tr->addAttribute('page_break', 1);
            }
            $line++;
        }
    }


    //
    //     Prints the table on PDF Document (STEP 3)
    //
    private function PrintFormatedTable(&$table) {
    // SimpleXMLElement $table: table to be printed
    //
        $table->x0 = sprintf('%0.40f', (double)$this->GetX());
        $table->y0 = sprintf('%0.40f', (double)$this->GetY());
        $table_color = self::GetHTMLColor($table['bordercolor']);

        if (DEBUG_FPDF_TABLE) {
            $this->Rect((double)$this->lMargin, (double)$this->tMargin,
                        (double)$this->w - ((double)$this->lMargin + (double)$this->rMargin),
                        (double)$this->h - ((double)$this->tMargin + (double)$this->bMargin));
        }

        $x0 = (double)$table['x0'];
        $y0 = false; // check after
        $w  = (double)$table['x0'] + (double)$table['width'] - (double)$x0;
        $h  = (double)0;
        $dif1 = (double)$table['border'] / (double)2;
        $dif2 = (double)$table['border'];

        // Print each line
        foreach ($table->tr as $tr) {
            if (isset($tr['page_break']) && (string)$tr['page_break']) {

                // Print table border
                if ((int)$table['border']) {
                    if ($y0 !== false && $h) {
                        $this->SetLineWidth((double)$table['border']);
                        $this->SetDrawColor($table_color['r'], $table_color['g'], $table_color['b']);
                        $this->Rect((double)$x0 - (double)$dif1, (double)$y0 - (double)$dif1,
                                    (double)$w + (double)$dif2, (double)$h + (double)$dif2);

                        $this->SetLineWidth(0.2);
                        $this->SetDrawColor(0, 0, 0);
                    }

                    $y0 = false;
                }

                $this->AddPage();
                if (DEBUG_FPDF_TABLE) {
                    $this->Rect((double)$this->lMargin, (double)$this->tMargin,
                                (double)$this->w - ((double)$this->lMargin + (double)$this->rMargin),
                                (double)$this->h - ((double)$this->tMargin + (double)$this->bMargin));
                }
            }

            $this->x = (double)$table['x0'];
            if ($y0 === false) {
                $this->y = (double)$this->y + (double)$table['margin_top'];
                $y = (double)$this->y;
                $y0 = (double)$y;
                $h  = (double)0;
            } else {
                $y = (double)$this->y;
            }

            // Print each column
            foreach ($tr as $cell) {
                $x = (double)$this->x;
                $this->PrintTableCell($table, $tr, $cell);
                $this->x = (double)$x + (double)$cell['width'];
                $this->y = (double)$y;
            }
            $this->y = (double)$y + (double)$tr['height'];
            $h += (double)$tr['height'];
        }

        // Print table border
        if ((int)$table['border']) {
            if ($y0 !== false && $h) {
                $this->SetLineWidth((double)$table['border']);
                $this->SetDrawColor($table_color['r'], $table_color['g'], $table_color['b']);
                $this->Rect((double)$x0 - (double)$dif1, (double)$y0 - (double)$dif1,
                            (double)$w + (double)$dif2, (double)$h + (double)$dif2);

                $this->SetLineWidth(0.2);
                $this->SetDrawColor(0, 0, 0);

                $this->y += (double)$table['border'];
            }
        }
//TODO
//self::DebugTable($table);
    }


    //
    //     Prints a table cell
    //
    private function PrintTableCell(&$table, &$line, &$cell) {
    // SimpleXMLElement $table: table to be printed
    // SimpleXMLElement $line: row to be printed
    // SimpleXMLElement $cell: cell to be printed
    //
        // Get Align
        $aligns = array('left'    => 'L',
                        'right'   => 'R',
                        'center'  => 'C',
                        'justify' => 'J');
        $align = $aligns[strtolower($cell['align'])];

        // Image offset
        if (isset($cell->img)) {
            $text = false;

            // Align
            switch ((string)$cell['align']) {
            case 'center':
                $x_offset = ((double)$cell['width'] / (double)2) - ((double)$cell->img['width'] / (double)2);
                $x_offset += (double)$cell['border'] + (double)$cell['padding'];
                break;
            case 'right':
                $x_offset = (double)$cell['width'] - (double)$cell->img['width'];
                $x_offset -= (double)$cell['border'] + (double)$cell['padding'];
                break;
            default:
            case 'justify':
            case 'left':
                $x_offset = (double)$cell['border'] + (double)$cell['padding'];
                break;
            }

            // Valign
            switch ((string)$cell['valign']) {
            case 'bottom':
                $y_offset = (double)$cell['height'] - (double)$cell->img['height'];
                $y_offset -= (double)$cell['border'] + (double)$cell['padding'];
                break;
            case 'middle':
                $y_offset = ((double)$cell['height'] / (double)2) - ((double)$cell->img['height'] / (double)2);
                break;
            case 'top':
            default:
                $y_offset = (double)$cell['border'] + (double)$cell['padding'];
                break;
            }

        // Text offset
        } else {
            $text = (string)$cell;
            if (preg_match('/.+/u', $text)) {
                $text = utf8_decode($text);
            }
            $text = preg_replace('/<br[\s]*\/>/i', "\n", $text);
            if ($text == '') {
                return;
            }
            // Get VAlign
            switch ((string)$cell['valign']) {
            case 'bottom':
                $margin_top = (double)$cell['height'] - (double)$cell['content_height'] - ((double)$cell['border'] + (double)$cell['padding']);
                break;
            case 'middle':
                $margin_top = ((double)$cell['height'] - (double)$cell['content_height']) / (double)2;
                break;
            case 'top':
            default:
                $margin_top = (double)$cell['padding'] + (double)$cell['border'];
                break;
            }
        }

        // Print background color
        $bx = (double)$this->x + ($text ? ((double)$cell['border'] / (double)2) : 0);
        $by = (double)$this->y + ($text ? ((double)$cell['border'] / (double)2) : 0);
        $bwidth  = (double)$cell['width']  - ($text ? ((double)$cell['border']) : 0);
        $bheight = (double)$cell['height'] - ($text ? ((double)$cell['border']) : 0);
        $bgcolor = self::GetHTMLColor($cell['bgcolor']);
        $this->SetFillColor($bgcolor['r'], $bgcolor['g'], $bgcolor['b']);
        $this->SetLineWidth(0);
        $this->Rect((double)$bx, (double)$by, (double)$bwidth, (double)$bheight, 'F');
        $this->SetLineWidth(0.2);
        $this->SetFillColor(0, 0, 0);

        // Print background image
        $bgimage = (string)$cell['bgimage'];
        if (!empty($bgimage)) {
            $src = realpath($bgimage);
            if (is_file($src)) {
                $vt_src = explode('.', basename($src));
                if ($count_vt_src = count($vt_src)) {
                    switch ($vt_src[count($vt_src) - 1]) {
                    case 'jpg':
                        $f = 'imagecreatefromjpeg';
                        break;
                    case 'gif':
                        $f = 'imagecreatefromgif';
                        break;
                    case 'png':
                        $f = 'imagecreatefrompng';
                        break;
                    default:
                        $f = '';
                    }
                    if ($f) {
                        $img = $f($src);
                        $img_width  = (double)imagesx($img);
                        $img_height = (double)imagesy($img);
                        $max_width  = (double)$cell['width'] - 2 * (double)$cell['border'];
                        $max_height = (double)$cell['height'] - 2 * (double)$cell['border'];
                        $need_cut = false;

                        $src_x = (double)0;
                        $src_y = (double)0;

                        // Right
                        if (strpos((string)$cell['bgposition'], 'r') !== false) {
                            if ($img_width > $max_width) {
                                $need_cut = true;
                                $src_x = $img_width - $max_width;
                                $img_width = $max_width;
                            }
                            $bx = (double)$this->x + (double)$cell['width'] - (double)$img_width - (double)$cell['border'];
                        // Center
                        } elseif (strpos((string)$cell['bgposition'], 'c') !== false) {
                            if ($img_width > $max_width) {
                                $need_cut = true;
                                $src_x = ($img_width - $max_width) / 2;
                                $img_width = $max_width;
                                $bx = (double)$this->x + (double)$cell['border'];
                            } else {
                                $bx = (double)$this->x + ((double)$cell['width'] - $img_width) / 2;
                            }
                        // Left
                        } else {
                            if ($img_width > $max_width) {
                                $need_cut = true;
                                $img_width = $max_width;
                            }
                            $bx = (double)$this->x + (double)$cell['border'];
                        }

                        // Bottom
                        if (strpos((string)$cell['bgposition'], 'b') !== false) {
                            if ($img_height > $max_height) {
                                $need_cut = true;
                                $src_y = $img_height - $max_height;
                                $img_height = $max_height;
                            }
                            $by = (double)$this->y + (double)$cell['height'] - (double)$img_height - (double)$cell['border'];
                        // Middle
                        } elseif (strpos((string)$cell['bgposition'], 'm') !== false) {
                            if ($img_height > $max_height) {
                                $need_cut = true;
                                $src_y = ($img_height - $max_height) / 2;
                                $img_height = $max_height;
                                $by = (double)$this->y + (double)$cell['border'];
                            } else {
                                $by = (double)$this->y + ((double)$cell['height'] - $img_height) / 2;
                            }
                        // Top
                        } else {
                            if ($img_height > $max_height) {
                                $need_cut = true;
                                $img_height = $max_height;
                            }
                            $by = (double)$this->y + (double)$cell['border'];
                        }

                        if ($need_cut) {
                            $newimg = imagecreatetruecolor($img_width, $img_height);
                            imagecopy($newimg, $img, 0, 0, $src_x, $src_y, $img_width, $img_height);
                            $this->SourceImage($newimg, (double)$bx, (double)$by);
                        } else {
                            $this->SourceImage($img, (double)$bx, (double)$by);
                        }
                    }
                }
            } else {
                $this->Error('Invalid image source "'.$bgimage.'"');
            }
        }

        // Print border
        if ((double)$cell['border'] > 0) {
            $bx = (double)$this->x + ((double)$cell['border'] / (double)2);
            $by = (double)$this->y + ((double)$cell['border'] / (double)2);
            $bwidth = (double)$cell['width'] - (double)$cell['border'];
            $bheight = (double)$cell['height'] - (double)$cell['border'];
            $border_color = self::GetHTMLColor($cell['bordercolor']);
            $this->SetDrawColor($border_color['r'], $border_color['g'], $border_color['b']);
            $line_width = (double)$this->LineWidth;
            $this->SetLineWidth((double)$cell['border']);
            $this->Rect((double)$bx, (double)$by, (double)$bwidth, (double)$bheight);
            $this->SetLineWidth((double)$line_width);
            $this->SetDrawColor(0, 0, 0);
        }

        // Print image
        if (isset($cell->img)) {
            $x = (double)$this->x + (double)$x_offset;
            $y = (double)$this->y + (double)$y_offset;
            $w = (double)$cell->img['width'];
            $h = (double)$cell->img['height'];
            $this->Image(realpath((string)$cell->img['src']), (double)$x, (double)$y, (double)$w, (double)$h);

        // Print text
        } else {

            // Print "padding"
            if (DEBUG_FPDF_TABLE) {
                $this->Rect((double)$this->x + ((double)$cell['border'] + (double)$cell['padding']),
                            (double)$this->y + (double)$margin_top,
                            (double)$cell['content_width'],
                            (double)$cell['content_height']);
            }

            // Color
            $color = self::GetHTMLColor($cell['color']);

            $this->x +=  (double)$cell['border'] + (double)$cell['padding'];
            $this->y += (double)$margin_top;
            $this->SetFont('', $cell['bold'] == '1' ? 'B' : '', (double)$cell['fontsize']);
            $this->SetTextColor($color['r'], $color['g'], $color['b']);
            $w = (double)$cell['content_width'];
            $h = (double)$cell['fontsize'];
            $this->MultiCell((double)$w, (double)$h, $text, DEBUG_FPDF_TABLE, $align);
        }
    }


    //
    //     Returns the constant correction of specified unit
    //
    private function getK($unit) {
    // String $unit: 'pt', 'px', 'mm', 'cm' or 'in'
    //
        switch ($unit) {
        case 'pt':
        case 'px':
            return 1;
        case 'mm':
            return 72/25.4;
        case 'cm':
            return 72/2.54;
        case 'in':
            return 72;
        }
        return 1;
    }


    //
    //     Returns the height of a MultiCell call
    //
    public function MultiCellHeight($w, $h, $txt) {
    // Float $w: width of cell
    // Float $h: height of cell
    // String $txt: text of cell
    //
        $correct = (double)0.5;
        $this2 = clone($this);
        $cw=&$this2->CurrentFont['cw'];
        if (preg_match('/.+/u', $txt)) {
            $txt = utf8_decode($txt);
        }
        if ($w==0)
            $w=$this2->w-$this2->rMargin-$this2->x;
        $wmax=($w-2*$this2->cMargin)*1000/$this2->FontSize;
        $s=str_replace("\r",'',$txt);
        $nb=strlen($s);
        if ($nb>0 && $s[$nb-1]=="\n")
            $nb--;
        $sep=-1;
        $i=0;
        $j=0;
        $l=0;
        $ns=0;
        $nl=1;
        $height = 0;
        while ($i<$nb) {
            //Get next character
            $c=$s{$i};
            if ($c=="\n") {
                //Explicit line break
                if ($this2->ws>0) {
                    $this2->ws=0;
                }
                $this2->Cell($w,$h,substr($s,$j,$i-$j),0,2);
                $height += $this2->lasth + (ceil($this2->lasth / $h) * $correct);
                $i++;
                $sep=-1;
                $j=$i;
                $l=0;
                $ns=0;
                $nl++;
                continue;
            }
            if ($c==' ') {
                $sep=$i;
                $ls=$l;
                $ns++;
            }
            $l+=$cw[$c];
            if ($l>$wmax) {
                //Automatic line break
                if ($sep==-1) {
                    if ($i==$j)
                        $i++;
                    if ($this2->ws>0) {
                        $this2->ws=0;
                    }
                    $this2->Cell($w,$h,substr($s,$j,$i-$j),0,2);
                    $height += $this2->lasth + (ceil($this2->lasth / $h) * $correct);
                } else {
                    $this2->Cell($w,$h,substr($s,$j,$sep-$j),0,2);
                    $height += $this2->lasth + (ceil($this2->lasth / $h) * $correct);
                    $i=$sep+1;
                }
                $sep=-1;
                $j=$i;
                $l=0;
                $ns=0;
                $nl++;
            } else {
                $i++;
            }
        }
        //Last chunk
        if ($this2->ws>0) {
            $this2->ws=0;
        }
        $this2->Cell($w,$h,substr($s,$j,$i-$j),0,2);
        $height += $this2->lasth + (ceil($this2->lasth / $h) * $correct);
        return $height;
    }


    //
    //     Create a image from a resource
    //
    public function SourceImage($img, $x, $y, $link = '') {
    // Resource $img: resource of image
    // Float $x: X position of image
    // Float $y: Y position of image
    // String $link: link of image (URL or identifier returned by AddLink)
    //
        static $i = 0;
        ++$i;

        //Put an image on the page
        $info = $this->_getimageinfo($img);
        $info['i'] = count($this->images) + 1;
        $this->images['resource'.$i] = $info;

        //Automatic width and height calculation if needed
        $w=$info['w']/$this->k;
        $h=$info['h']/$this->k;

        //Flowing mode
        if($y===null)
        {
            if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
            {
                //Automatic page break
                $x2=$this->x;
                $this->AddPage($this->CurOrientation,$this->CurPageFormat);
                $this->x=$x2;
            }
            $y=$this->y;
            $this->y+=$h;
        }
        if($x===null)
            $x=$this->x;

        $this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q',$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']));
        if($link)
            $this->Link($x,$y,$w,$h,$link);
    }


    //
    //     Get info from an image resource
    //
    protected function _getimageinfo($img) {
    // Resource $img: image resource
    //
        ob_start();
        imagejpeg($img);
        $data = ob_get_contents();
        ob_end_clean();

        return array('w' => imagesx($img),
                     'h' => imagesy($img),
                     'cs' => 'DeviceRGB',
                     'bpc' => 8,
                     'f' => 'DCTDecode',
                     'data' => $data);
    }


    //
    //     Prints the table as XML
    //
    static private function DebugTable(&$table) {
    // SimpleXMLElement $table: table to be printed
    //
        header('Content-type: text/xml');
        echo $table->asXML();
        exit(0);
    }

}//class
