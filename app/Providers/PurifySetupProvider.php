<?php

namespace App\Providers;

use HTMLPurifier_HTMLDefinition;
use Stevebauman\Purify\Facades\Purify;
use Illuminate\Support\ServiceProvider;
use HTMLPurifier_HTML5Config;

class PurifySetupProvider extends ServiceProvider
{
    const DEFINITION_ID = 'tiptap-editor';
    const DEFINITION_REV = 1;

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        /** @var \HTMLPurifier $purifier */
        $purifier = Purify::getPurifier();

        /** @var \HTMLPurifier_Config $config */
        $config = HTMLPurifier_HTML5Config::createDefault();

        $config->set('HTML.DefinitionID', static::DEFINITION_ID);
        $config->set('HTML.DefinitionRev', static::DEFINITION_REV);
        if(app()->environment() === 'local') $config->set('Cache.DefinitionImpl', null);
                                                            // disable cache for testing

        $config->set('HTML.SafeIframe', true);
        $config->set('URI.SafeIframeRegexp', '%^(http://|https://|//)(www.youtube.com/embed/|player.twitch.tv/|clips.twitch.tv/)%');

        if ($def = $config->maybeGetRawHTMLDefinition()) {
            $this->setupDefinitions($def);
        }

        $purifier->config = $config;
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Adds elements and attributes to the HTML purifier definition.
     *
     * @param HTMLPurifier_HTMLDefinition $def
     */
    protected function setupDefinitions(HTMLPurifier_HTMLDefinition $def)
    {
        $def->addElement('u', 'Inline', 'Inline', 'Common');

        $def->addElement('s', 'Inline', 'Inline', 'Common');
    }
}
