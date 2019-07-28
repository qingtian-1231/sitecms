<?php
namespace woo\lib;

class Assign
{
    public $varsReturned = false;
    public $js = [];
    public $css = [];
    public $deferJs = [];

    public function addJs($path, $defer = false)
    {
        if ($defer) {
            if (!is_array($path)) {
                array_push($this->deferJs, $path);
            } else {
                $this->deferJs = array_merge($this->deferJs, $path);
            }
            $this->deferJs = array_unique($this->deferJs);            
        } else {
            if (!is_array($path)) {
                array_push($this->js, $path);                
            } else {
                $this->js = array_merge($this->js, $path);
            }
            $this->js = array_unique($this->js);
        }
        
    }

    public function removeJs($path, $defer = false)
    {
        if ($defer) {
            if (!is_array($path)) {
                $this->deferJs = array_diff($this->deferJs, [$path]);
            } else {
                $this->deferJs = array_diff($this->deferJs, $path);
            }
            
        } else {
            $this->js = array_diff($this->js, [$path]);
        }
    }

    public function addCss($path)
    {
        if (!is_array($path)) {
            array_push($this->css, $path);
        } else {
            $this->css = array_merge($this->css, $path);
        }
        $this->css = array_unique($this->css);    
    }

    public function removeCss($path)
    {
        if (!is_array($path)) {
            $this->css = array_diff($this->css, [$path]);
        } else {
            $this->css = array_diff($this->css, $path);
        }
    }
}
