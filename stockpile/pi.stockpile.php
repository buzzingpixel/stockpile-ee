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
     * Creates a unique key (tag)
     */
    public function create_unique_key()
    {
        // Create a group name to store this key group in
        $group = $this->templateService->fetch_param('group', 'none');
        $group = "keyGroup_{$group}";

        // Create a unique key
        $uniqueId = uniqid('', false);

        // Store the unique key
        $this->storage->set($uniqueId, $uniqueId, $group);

        // Return parsed variables
        return $this->templateService->parse_variables(
            $this->templateService->tagdata,
            array(
                array(
                    'stockpile:unique_key' => $uniqueId,
                ),
            )
        );
    }

    /**
     * Gets a unique key group (tag)
     */
    public function get_unique_key_group()
    {
        // Get the group name
        $group = $this->templateService->fetch_param('group', 'none');
        $group = "keyGroup_{$group}";

        // Get the keys for this group from storage
        $keys = $this->storage->getStorageArray($group);

        // Set up a variables array
        $vars = array();
        $totalResults = count($keys);
        $counter = 0;

        // Create variables array
        foreach ($keys as $key) {
            $vars[] = array(
                'stockpile:unique_key_index' => $counter,
                'stockpile:unique_key_count' => $counter + 1,
                'stockpile:unique_key_total_results' => $totalResults,
                'stockpile:unique_key' => $key,
            );
            $counter++;
        }

        // Return parsed variables
        return $this->templateService->parse_variables(
            $this->templateService->tagdata,
            $vars
        );
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

    /**
     * Get stockpile info
     */
    public function info()
    {
        // Create variables array
        $vars = array();

        // Iterate through each storage set and set up info variables
        foreach ($this->storage->getStorageArray() as $key => $val) {
            $count = count($val);
            $vars["stockpile:{$key}:total"] = $count;
            $vars["stockpile:{$key}:last_index"] = $count - 1;
        }

        // Return parsed variables
        return $this->templateService->parse_variables(
            $this->templateService->tagdata,
            array($vars)
        );
    }
}
