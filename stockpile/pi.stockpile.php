<?php

/**
 * @author TJ Draper <tj@buzzingpixel.com>
 * @copyright 2018 BuzzingPixel, LLC
 * @license http://www.apache.org/licenses/LICENSE-2.0
 */

namespace buzzingpixel\stockpile;

use buzzingpixel\stockpile\services\Storage;

/**
 * Class Stockpile
 */
class Stockpile
{
    /** @var Storage $storage */
    private $storage;

    /** @var \EE_Template $templateService */
    private $templateService;

    public function __construct()
    {
        $this->storage = Storage::getInstance();
        $this->templateService = ee()->TMPL;
    }

    /**
     * Create next index
     * @param string $key
     * @return string
     * @throws \Exception
     */
    private function createIndex($key = null)
    {
        // Key is required
        if (! $key) {
            throw new \Exception('"key" is required');
        }

        // Create the key if it doesn't exist
        if ($this->storage->get($key) === null) {
            $this->storage->set(array(), $key);
        }

        // Create the index
        $index = uniqid('', false);

        // Get the array
        $array = $this->storage->get($key);

        // Add the index
        $array[$index] = array();

        // Re-set the array
        $this->storage->set($array, $key);

        // Return the index
        return $index;
    }

    /**
     * Create index (tag)
     * @return string
     * @throws \Exception
     */
    public function create_index()
    {
        // Get key
        $key = $this->templateService->fetch_param('key');

        // Return parsed variables
        return $this->templateService->parse_variables(
            $this->templateService->tagdata,
            array(
                array(
                    'stockpile:index' => $this->createIndex($key),
                    'stockpile:key' => $key,
                ),
            )
        );
    }

    /**
     * Add to storage (tag)
     * @throws \Exception
     */
    public function add()
    {
        // Get var name
        $varName = $this->templateService->fetch_param('var_name');

        // Var name is required
        if (! $varName) {
            throw new \Exception('"var_name" is required');
        }

        // Get Key
        $key = $this->templateService->fetch_param('key');

        // Key is required
        if (! $key) {
            throw new \Exception('"key" is required');
        }

        // Get index
        $index = $this->templateService->fetch_param('index') ?:
            $this->createIndex($key);

        // Get val
        $val = trim($this->templateService->fetch_param(
            'val',
            $this->templateService->tagdata
        ));

        // Get the array
        $array = $this->storage->get($key);

        // Set the item
        $array[$index][$varName] = $val;

        // Re-set the array
        $this->storage->set($array, $key);
    }

    /**
     * Get (tag)
     * @return string
     * @throws \Exception
     */
    public function get()
    {
        // Get Key
        $key = $this->templateService->fetch_param('key');

        // Key is required
        if (! $key) {
            throw new \Exception('"key" is required');
        }

        // Get the array
        $array = $this->storage->get($key);

        // If there's nothing, we should stop
        if (! $array) {
            return null;
        }

        // Set the total_results
        $totalResults = count($array);

        // Get all variables, set total results and count, namespace
        $vars = array();
        $newArray = array();
        $counter = 0;
        foreach ($array as $key => $val) {
            $newArray[$counter]['stockpile:index'] = $counter;
            $newArray[$counter]['stockpile:count'] = $counter + 1;
            $newArray[$counter]['stockpile:total_results'] = $totalResults;
            foreach ($val as $var => $varVal) {
                $vars["stockpile:{$var}"] = "stockpile:{$var}";
                $newArray[$counter]["stockpile:{$var}"] = $varVal;
            }
            $counter++;
        }

        // Make sure all iterations have all variables
        foreach ($newArray as $key => $val) {
            foreach ($vars as $var) {
                if (isset($val[$var])) {
                    continue;
                }
                $newArray[$key][$var] = null;
            }
        }

        // Return parsed variables
        return $this->templateService->parse_variables(
            $this->templateService->tagdata,
            $newArray
        );
    }
}
