<?php

namespace Tonic;

/**
 * Cache resource metadata between invocations
 *
 * This class exports the resource metadata and writes it to disk for including in a later request.
 */
class MetadataCacheInclude implements MetadataCache
{
    private $filename;

    public function __construct($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Is there already cache file
     * @return boolean
     */
    public function isCached()
    {
        return is_readable($this->filename);
    }

    /**
     * Load the resource metadata
     * @return str[]
     */
    public function load()
    {
	    return include $this->filename;
    }

    /**
     * Save resource metadata to disk
     * @param  str[]   $resources Resource metadata
     * @return boolean
     */
    public function save($resources)
    {
		return file_put_contents($this->filename, '<?php return ' . var_export($resources, true) . ';');
    }

    public function clear()
    {
        @unlink($this->filename);
    }

    public function __toString()
    {
        return 'Metadata for '.count($this->load()).' resources stored in file "'.$this->filename.'" at '.date('r', filemtime($this->filename));
    }

}
