<?php
/******************************************************************************
*
* DailyboothBackup - backup your dailybooth pictures
* Copyright (C) 2010 Philipp Waldhauer
*
* This program is free software; you can redistribute it and/or modify it
* under the terms of the GNU General Public License as published by the
* Free Software Foundation; either version 2 of the License, or
* (at your option) any later version.
*
* This program is distributed in the hope that it will be useful, but
* WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
* or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for
* more details.
*
* You should have received a copy of the GNU General Public License along with
* this program; if not, write to the Free Software Foundation, Inc.,
* 51 Franklin St, Fifth Floor, Boston, MA 02110, USA
*
*****************************************************************************/

class DailyboothBackup {

    private $base = 'http://dailybooth.com/';
    private $username = '';
    private $verbose = false;

    /**
     * Parses the users stream and returns an array of urls of all picture pages.
     * @return array an array of urls to all picture pages 
     */
    public function getPages() {
        $this->log('Getting pages...');
        $urls = array();

        $page = 0;
        while (true) {
            $url = $this->getPageUrl($page);

            $this->log('Trying page #' . $page);

            $content = file_get_contents($url);

            preg_match_all('#/' . $this->username . '/([0-9]+)"><img#isU', $content, $match);

            $count = count($match[0]);

            if ($count == 0) {
                $this->log('No more images here. Ready.');
                break;
            }


            for ($i = 0; $i < $count; $i++) {
                $urls[] = $match[1][$i];
            }

            $page++;
        }

        $this->log('Got ' . count($urls) . ' pages.');

        return $urls;
    }

    /**
     * Parses the given picture pages for the urls of the original pictures.
     * @param array $pages a array containing urls of picture pages
     * @return array an array of urls to all large pictures
     */
    public function getPictures(array $pages) {
        $this->log('Getting pictures...');
        $large = array();

        foreach ($pages as $pic) {
            $url = $this->getPicturePageUrl($pic);

            $content = file_get_contents($url);

            preg_match('#<div id="picture"><img src="(.*)" alt="(.*)" />#isU', $content, $match);

            $large[$match[2]] = $match[1];


            $this->log('Found one: ' . $match[1]);
        }

        $this->log('Got ' . count($large) . ' pictures.');

        return $large;
    }

    /**
     * Downloads the given pictures into a given directory.
     * @param array $pictures a array containing urls of pictures
     * @param type $directory a writable directory to put the pictures
     */
    public function downloadPictures(array $pictures, $directory) {
        if (!is_writable($directory)) {
            die('Unable to write directory: ' . $directory);
        }

        $this->log('Downloading images...');

        foreach ($pictures as $name => $url) {
            $bin = file_get_contents($url);

            $fp = fopen($directory . '/' . $name . '.jpg', 'wb');
            fwrite($fp, $bin);
            fclose($fp);

            $this->log('Downloaded: ' . $name);
        }


        $this->log('Download ready!');
    }

    /**
     * Sets the username.
     * @param string $username the username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * Sets the verbosity mode.
     * 
     * Default: false
     * 
     * @param boolean $verbose verbosity mode
     */
    public function setVerbose($verbose) {
        $this->verbose = $verbose;
    }

    private function log($str) {
        if ($this->verbose == false) {
            return;
        }

        echo $str . "\n";
    }

    private function getPageUrl($page) {
        return $this->base . $this->username . '/page/' . $page;
    }

    private function getPicturePageUrl($pic) {
        return $this->base . $this->username . '/' . $pic;
    }

}