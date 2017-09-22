<?php
/**
 * Created by PhpStorm.
 * User: alex
 * Date: 20.09.17
 * Time: 12:34
 */

namespace Views;

use League\CLImate\CLImate;

class CliView
{
    /**
     * @var CLImate
     */
    private $cliMate;

    /**
     * @param CLImate $cliMate
     */
    public function __construct(CLImate $cliMate)
    {

        $this->cliMate = $cliMate;
    }

    public function render($data)
    {
        $this->arrayObjectsToArray($data);
        $this->prepareArrayToPrint($data);
        $this->cliMate->table($data);
        
    }

    public function draw()
    {
        $this->cliMate->addArt(__DIR__ . '/../../textImages');
        $this->cliMate->clear();

        $this->cliMate->magenta()->bold()->out('   Made by Alexandr Gavuka');
        $this->cliMate->border('-');
        $this->cliMate->green()->draw('sqltomongo');
        $this->cliMate->border('-');
    }

    /**
     * Control for function arrayToString
     *
     * @param $data
     */
    private function prepareArrayToPrint(&$data)
    {
        foreach ($data as &$datum) {
            if (is_array($datum)) {
                foreach ($datum as &$item) {
                    if (is_array($item)) {
                        $item = $this->arrayToString($item);
                    }
                }
            }
        }
    }

    /**
     * Converting multidimensional data sets to a string for output in a table
     *
     * @param $data
     * @return null|string
     */
    private function arrayToString($data)
    {
        $string = null;
        foreach ($data as $key => &$datum) {
            if (is_array($datum)) {
                $string .= $key . ':{ ' . $this->arrayToString($datum) . ' } ';
            } else {
                if ($datum === end($data)) {
                    $string .= $key . ':' . $datum;
                } else {
                    $string .= $key . ':' . $datum . ', ';
                }
            }
        }

        return $string;
    }

    /**
     * array of objects to simple multidimensional array
     *
     * @param $data
     */
    private function arrayObjectsToArray(&$data)
    {
        foreach ($data as &$datum) {
            if (is_object($datum)) {
                $datum = (array) $datum;
            }
            if (is_array($datum)) {
                $this->arrayObjectsToArray($datum);
            }
        }
    }

    /**
     * print error and write greeting for input in CLI
     *
     * @param string $message
     */
    public function printErrorInCli(string $message): void
    {
        $this->cliMate->error($message);
    }
}
