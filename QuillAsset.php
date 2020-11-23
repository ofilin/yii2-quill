<?php

namespace ofilin\quill;

use yii\web\AssetBundle;

class QuillAsset extends AssetBundle
{

    public $theme;

    public $sourcePath = '@ofilin/quill/src/dist';
    
    public function init()
    {
        parent::init();
        
        if (class_exists('yii\\bootstrap4\\BootstrapAsset')){
            $this->depends[] = 'yii\bootstrap4\BootstrapAsset';
        } else {
            $this->depends[] = 'yii\bootstrap\BootstrapAsset';
        }
    }

    public function registerAssetFiles($view)
    {
        switch ($this->theme) {
            case Quill::THEME_SNOW:
                $this->css = ['quill.snow.css'];
                break;
            case Quill::THEME_BUBBLE:
                $this->css = ['quill.bubble.css'];
                break;
            default:
                $this->css = ['quill.core.css'];
        }

        $this->js = ['quill.min.js'];

        parent::registerAssetFiles($view);
    }

    public $depends = [];
}
