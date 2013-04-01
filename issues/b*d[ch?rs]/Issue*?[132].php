<?php

namespace Tonic;

/**
 * This issues tests square braces in filenames and directories of Resource classes to load
 *
 * @uri /issue132
 */
class Issue132 extends Resource {

    /**
     * @method GET
     */
    function handle()
    {
        return 'loaded';
    }

}