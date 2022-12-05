<?php
class KwfCloudflare_Events extends Kwf_Events_Subscriber
{
    public function getListeners()
    {
        $ret = parent::getListeners();
        $ret[] = array(
            'class' => null,
            'event' => 'Kwf_Component_Event_CreateMediaUrl',
            'callback' => 'onCreateMediaUrl'
        );
        $ret[] = array(
            'class' => null,
            'event' => 'Kwf_Events_Event_CreateAssetsPackageUrls',
            'callback' => 'onCreateAssetsPackageUrls'
        );
        $ret[] = array(
            'class' => null,
            'event' => 'Kwf_Events_Event_CreateAssetUrl',
            'callback' => 'onCreateAssetUrl'
        );
        $ret[] = array(
            'class' => null,
            'event' => 'Kwf_Events_Event_Media_Changed',
            'callback' => 'onMediaChanged'
        );
        return $ret;
    }

    public function onCreateMediaUrl(Kwf_Component_Event_CreateMediaUrl $ev)
    {
        $cloudflareDomain = $ev->component->getBaseProperty('cloudflare.domain');
        $component = $ev->component;
        $isProtected = false;
        while ($component) {
            if ($component->getPlugins('Kwf_Component_Plugin_LoginAbstract_Component')) {
                $isProtected = true;
                $component = null;
            } else {
                $component = ($component->isPage) ? null : $component->parent;
            }
        }
        if ($cloudflareDomain && $ev->component->isVisible() && !$isProtected) {
            $ev->url = '//'.$cloudflareDomain.$ev->url;
        }
    }

    public function onCreateAssetUrl(Kwf_Events_Event_CreateAssetUrl $ev)
    {
        if ($ev->subroot) {
            $cloudflareDomain = $ev->subroot->getBaseProperty('cloudflare.domain');
            if ($cloudflareDomain) {
                $ev->url = '//'.$cloudflareDomain.$ev->url;
            }
        }
    }

    public function onCreateAssetsPackageUrls(Kwf_Events_Event_CreateAssetsPackageUrls $ev)
    {
        $cloudflareDomain = null;
        if ($ev->subroot) {
            $cloudflareDomain = $ev->subroot->getBaseProperty('cloudflare.domain');
        } else {
            $cloudflareDomain = Kwf_Config::getValue('cloudflare.domain');
        }
        if ($cloudflareDomain) {
            $ev->prefix = '//'.$cloudflareDomain;
        }
    }
}
