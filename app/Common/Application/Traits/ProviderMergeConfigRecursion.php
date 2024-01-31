<?php

namespace App\Common\Application\Traits;

trait ProviderMergeConfigRecursion
{
    public function mergeConfigRecursion($arr, $path, $config = null): void
    {
        if (is_null($config)) {
            $config = $this->app->make('config');
        }

        foreach ($arr as $key => $value) {
            $path_cur = $path . '.' . $key;
            if (is_array($value)) {
                $this->mergeConfigRecursion($value, $path_cur, $config);
                continue;
            }
            $config->set($path_cur, $value);
        }
    }
}
