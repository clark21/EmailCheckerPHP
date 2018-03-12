<?php

namespace EmailChecker;
/* Telnet Class
 *
 * author: Clark Galgo <clark@dev-engine.net>
 *
 */
class Lookup {
    public $host = NULL;

    /*
     * construct
     *
     * @param string $host
     */
    public function __construct ($host) {
        $this->host = $host;
    }

    /*
     * get mx records
     *
     * @return array
     */
    public function getMxRecords () {
        if (!$this->host) {
            throw new \Exception('Host is not set.');
            return;
        }

        
        // dig it!!! dig it!!!
        exec('dig ' . $this->host . ' mx', $output);

        // find mx record
        $mx = [];
        foreach ($output as $record) {
            if (preg_match('/IN\sMX\s\d(.*)/i', $record)) {
                // match record
                preg_match('/IN\sMX\s\d*(.*)/i', $record, $match);
                $mx[] = trim($match[1]);
            }
            
        }

        // cant find mx record :'(
        if (empty($mx)) {
            throw new \Exception('no mx record found!');
            return;
        }

        return $mx;
    }
    
}
