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
     * @deprecated
     */
    public function getPages() {
        $this->log('Getting pages...');
        $urls = array();

        for ($page = 0; true; $page++) {
            $url = $this->getPageUrl($page);

            $this->log('Trying page #' . $page);

            $content = file_get_contents($url);

            preg_match_all('#/' . $this->username . '/([0-9]+)"><div class=\'rounder\'#isU', $content, $match);

            $count = count($match[0]);

            if ($count == 0) {
                $this->log('No more images here. Ready.');
                break;
            }


            for ($i = 0; $i < $count; $i++) {
                $urls[] = $match[1][$i];
            }

        }

        $this->log('Got ' . count($urls) . ' picture pages.');

        return $urls;
    }

    /**
     * Parses all pages of the given profile page for images
     * and returns an array of picture urls for downloading
     * @return array of picture urls
    */
    public function getPicturesFast() {
	$this->log('Getting pictures (fast mode, yeah)...');
	$urls = array();

	for ($page = 0; true; $page++) {
		$url = $this->getPageUrl($page);

		$this->log('Trying page #'. $page);

		$content = file_get_contents($url);

		preg_match_all('#<div class=\'rounder\'.*><img src="(.*)"#', $content, $match);

		$count = count($match[0]);
            	
		if ($count == 0) {
                	$this->log('No more images here. Ready.');
	                break;
		}

            	for ($i = 0; $i < $count; $i++) {
             		$urls[] = str_replace('medium', 'large', $match[1][$i]);
           	}

	}

	$this->log('Got '. count($urls) . ' pictures.');

	return $urls;
    }

    /**
     * Parses the given picture pages for the urls of the original pictures.
     * @param array $pages a array containing urls of picture pages
     * @return array an array of urls to all large pictures
     * @deprecated
     */
    public function getPictures(array $pages) {
        $this->log('Getting pictures...');
        $large = array();

        foreach ($pages as $pic) {
            $url = $this->getPicturePageUrl($pic);

            $content = file_get_contents($url);

            preg_match('#<img id="main_picture" src="(.*)" />#isU', $content, $match);

            $large[] = $match[1];

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
            $this->log('Unable to write directory: ' . $directory, true);
            exit;
        }

        $this->log('Downloading images...');

	$pictures = array_reverse($pictures);

        foreach ($pictures as $name => $url) {
            if (copy($url, $directory . '/' . $name . '.jpg')) {
                $this->log('Downloaded: ' . $name);
            } else {
                $this->log('Download failed: ' . $name);
            }
        }
        
        $this->log('Download complete!');
    }

    /**
     * Sets the username.
     * @param string $username the username
     */
    public function setUsername($username) {
        $this->username = (string) $username;
    }

    /**
     * Sets the verbosity mode.
     * 
     * Default: false
     * 
     * @param boolean $verbose verbosity mode
     */
    public function setVerbose($verbose) {
        $this->verbose = (bool) $verbose;
    }

    public function log($str, $force = false) {
        if ($this->verbose == true || $force == true) {
            echo $str . "\n";
        }
    }

    private function getPageUrl($page) {
        return $this->base . $this->username . '/page/' . $page;
    }

    private function getPicturePageUrl($pic) {
        return $this->base . $this->username . '/' . $pic;
    }

}
