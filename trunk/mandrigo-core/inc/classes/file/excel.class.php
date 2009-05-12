<?php

/**
 * @file			excel.pkg.php
 * @maintainer 		Kevin Wijesekera
 * @author			Ignatius Teo
 * @copyright 		2004 act28.com
 * @edited			5-12-2009
 
 ###################################
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.
 
 You should have received a copy of the GNU General Public License
 along with this program.  If not, see http://www.gnu.org/licenses/.
 ###################################
 */

if(!defined('STARTED')){
	die();
}

class excel{
	
	private $document;
	const XLS_EXTENSION		= '.xls';
	
	function __construct(){
		stream_wrapper_register("xlsfile", "xlsStream");
		$this->document=array();
	}
	
	function excel_addData($column,$row,$data){
		if(empty($this->document[$row])){
			$this->document[$row]=array();
		}
		$this->document[$row][$column]=$data;
	}
	
	function excel_write($file=false){
		$output=false;
		if(!$file){
			$output=true;
			$file=$GLOBALS['MG']['CFG']['PATH']['TMP'].'/'.md5(uniqid(rand(),true)).excel::XLS_EXTENSION;
		}
		if(!$f=fopen('xlsfile://'.$file,'wb')){
			trigger_error('(EXCEL): Could not open file for writing: '.$file);
			return false;
		}
		fwrite($f, serialize($this->document));
		fclose($f);
		if($output){
			$output='';
			if(!$f=fopen($file,'r')){
				trigger_error('(EXCEL): Could not open file for reading: '.$file);
				return false;
			}
			while(!feof($f)){
				$output=fgets($f);
			}
			fclose($f);
			$GLOBALS['MG']['LANG']['CONTENT_TYPE']='application/x-msexcel';
			$GLOBALS['MG']['LANG']['PRAGMA']='no-cache';
			$GLOBALS['MG']['LANG']['CACHE_CONTROL']='no-cache, must-revalidate';
			$GLOBALS['MG']['LANG']['CONTENT_DISPOSITION']='attachment; filename="' . basename($file) . '" )';
			$GLOBALS['MG']['PAGE']['NOSITETPL']=true;
			return $output;
		}
		else{
			return true;
		}
	}
}


/**
 * MS-Excel stream handler
 * This class read/writes a data stream directly
 * from/to a Microsoft Excel spreadsheet
 * opened with the xlsfile:// protocol
 * This is used to export associative array data directly to MS-Excel
 * @requires    PHP 4 >= 4.3.2
 * @author      Ignatius Teo            <ignatius@act28.com>
 * @modifiedby	Kevin Wijesekera
 * @copyright   (C)2004 act28.com       <http://act28.com>
 * @version     0.3
 * @date        20 Jan 2005
 * $Id: excel.php,v 1.3 2005/01/20 09:58:58 Owner Exp $
 */
class xlsStream
{
    /* private */
    var $position = 0;          // stream pointer
    var $mode = "rb";           // default stream open mode
    var $xlsfilename = null;    // stream name
    var $fp = null;             // internal stream pointer to physical file
    var $buffer = null;         // internal write buffer
    var $endian = "s";	// little | unknown | big endian mode

	function _detect(){
		// A hex number that may represent 'abyz'
		$abyz = 0x6162797A;

		// Convert $abyz to a binary string containing 32 bits
		// Do the conversion the way that the system architecture wants to
		switch (pack ('L', $abyz)){
		    // Compare the value to the same value converted in a Little-Endian fashion
		    case pack ('V', $abyz):
		        $this->endian = "v";
		    break;

		    // Compare the value to the same value converted in a Big-Endian fashion
		    case pack ('N', $abyz):
		        $this->endian = "s";
		    break;

		    default:
		        $this->endian = "s";
		    break;
		}
	}

    /**
     * called by fopen() to the stream
     * @param   (string)    $path           file path
     * @param   (string)    $mode           stream open mode
     * @param   (int)       $options        stream options (STREAM_USE_PATH |
     *                                      STREAM_REPORT_ERRORS)
     * @param   (string)    $opened_path    stream opened path
     */
    function stream_open($path, $mode, $options, &$opened_path){
        $this->xlsfilename = ereg_replace('xlsfile://','',$path);
        $this->position = 0;
        $this->mode = $mode;

		$this->_detect();	// detect endian mode

        //@TODO: test for invalid mode and trigger error if required

        // open underlying resource
        $this->fp = @fopen($this->xlsfilename, $this->mode);
        if (is_resource($this->fp)){
            // empty the buffer
            $this->buffer = "";

            if (preg_match("/^w|x/", $this->mode)){
                // write an Excel stream header
                $str = pack(str_repeat($this->endian, 6), 0x809, 0x8, 0x0, 0x10, 0x0, 0x0);
                fwrite($this->fp, $str);
                $opened_path = $this->xlsfilename;
                $this->position = strlen($str);
            }
        }
        return is_resource($this->fp);
    }

    /**
     * read the underlying stream resource (automatically called by fread/fgets)
     * @todo    modify this to convert an excel stream to an array
     * @param   (int)       $byte_count     number of bytes to read (in 8192 byte blocks)
     */
    function stream_read($byte_count){
        if (is_resource($this->fp) && !feof($this->fp)){
            $data .= fread($this->fp, $byte_count);
            $this->position = strlen($data);
        }
        return $data;
    }

    /**
     * called automatically by an fwrite() to the stream
     * @param   (string)    $data           serialized array data string
     *                                      representing a tabular worksheet
     */
    function stream_write($data){
        // buffer the data
        $this->buffer .= $data;
        $bufsize = strlen($data);
        return $bufsize;
    }

    /**
     * pseudo write function to manipulate the data
     * stream before writing it
     * modify this to suit your data array
     * @access  private
     * @param   (array)     $data           associative array representing
     *                                      a tabular worksheet
     */
    function _xls_stream_write($data){
        if (is_array($data) && !empty($data)){
            $row = 0;
            $size=0;
            foreach (array_values($data) as $_data){
                if (is_array($_data) && !empty($_data)){
                    if ($row == 0){
                        // write the column headers
                        foreach (array_keys($_data) as $col => $val){
                            // next line intentionally commented out
                            // since we don't want a warning about the
                            // extra bytes written
                            // $size += $this->write($row, $col, $val);
                            $this->_xlsWriteCell($row, $col, $val);
                        }
                        $row++;
                    }

                    foreach (array_values($_data) as $col => $val){
                        $size += $this->_xlsWriteCell($row, $col, $val);
                    }
                    $row++;
                }
            }
        }
        return $size;
    }

    /**
     * Excel worksheet cell insertion
     * (single-worksheet supported only)
     * @access  private
     * @param   (int)       $row            worksheet row number (0...65536)
     * @param   (int)       $col            worksheet column number (0..255)
     * @param   (mixed)     $val            worksheet row number
     */
    function _xlsWriteCell($row, $col, $val){
        if (is_float($val) || is_int($val)){
            // doubles, floats, integers
            $str  = pack(str_repeat($this->endian, 5), 0x203, 14, $row, $col, 0x0);
            $str .= pack("d", $val);
        }
        else{
            // everything else is treated as a string
            $l    = strlen($val);
            $str  = pack(str_repeat($this->endian, 6), 0x204, 8 + $l, $row, $col, 0x0, $l);
            $str .= $val;
        }
        fwrite($this->fp, $str);
        $this->position += strlen($str);
        return strlen($str);
    }

    /**
     * called by an fclose() on the stream
     */
    function stream_close(){
        if (preg_match("/^w|x/", $this->mode)){
            // flush the buffer
            $bufsize = $this->_xls_stream_write(unserialize($this->buffer));

            // ...and empty it
            $this->buffer = null;

            // write the xls EOF
            $str = pack(str_repeat($this->endian, 2), 0x0A, 0x00);
            $this->position += strlen($str);
            fwrite($this->fp, $str);
        }

        // ...and close the internal stream
        return fclose($this->fp);
    }

    function stream_eof(){
        $eof = true;
        if(is_resource($this->fp)){
            $eof = feof($this->fp);
        }
        return $eof;
    }
}