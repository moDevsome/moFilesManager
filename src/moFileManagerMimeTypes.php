<?php

/*
 * moFilesManager
 * 
 * A small library for handling files and folders more easier
 * 
 * Required PHP Version : PHP 7.0 or higher
 * 
 * @author Mickaël Outhier <contact@mickael-outhier.fr>
 *
 * @copyright (c) 2021 Mickaël Outhier (contact@mickael-outhier.fr)
 *  
 * @license The MIT License (MIT)
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of
 * this software and associated documentation files (the "Software"), to deal in
 * the Software without restriction, including without limitation the rights to
 * use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
 * the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 * FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
 * COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
 * IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
 * CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

namespace moFilesManager;

class FileMimeTypes {

    /**
     * Return the file type based on the file mime_type
     * 
     * @param   string  $mime_type
     * 
     * @return  string  $file_type
	 *
	 * @since   1.1.0
    */
    public function getType($mime_type) : string {

        $mime_segments = explode('/', $mime_type);

        if($mime_segments[0] === 'application') {

            switch($mime_segments[1]) {

                case 'xhtml+xml' :
                return 'xhtml';
                break;

                case 'x-shockwave-flash' :
                return 'flash';
                break;

                case 'ld+json' :
                return 'json';
                break;

                case 'vnd.ms-excel' :
                return 'msexcel';
                break;

                case 'vnd.ms-powerpoint' :
                return 'mspowerpoint';
                break;

                case 'vnd.mozilla.xul+xml' :
                return 'mozilla-xul';
                break;

                case 'x-7z-compressed' :
                case 'x-rar-compressed' :
                case 'x-zip-compressed' :
                case 'x-zip' :
                return 'zip';
                break;

                default :
                return $mime_segments[1];
                break;
            }

        }
        elseif($mime_segments[0] === 'text') {

            switch($mime_segments[1]) {

                case 'plain' :
                return 'text';
                break;

                case 'x-php' :
                return 'php';
                break;

                default :
                return $mime_segments[1];
                break;
            }

        }
        elseif($mime_segments[0] === 'audio') {

            switch($mime_segments[1]) {

                case 'mpeg' :
                case 'x-ms-wma' :
                case 'vnd.rn-realaudio' :
                case 'x-wav' :
                return 'audio';
                break;

                default :
                return $mime_segments[1];
                break;
            }

        }
        elseif($mime_segments[0] === 'image') {

            switch($mime_segments[1]) {

                case 'gif' :
                case 'jpeg' :
                case 'png' :
                case 'tiff' :
                case 'vnd.microsoft.icon' :
                case 'x-icon' :
                case 'svg+xml' :
                return 'image';
                break;

                default :
                return $mime_segments[1];
                break;
            }

        }
        else {

            return $mime_segments[0];

        }

        return 'undefined';
    
    }
}
?>
