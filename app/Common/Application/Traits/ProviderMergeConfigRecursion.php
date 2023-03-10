<?php

namespace App\Common\Application\Traits;

trait ProviderMergeConfigRecursion
{
    public function mergeConfigRecursion($arr, $path): void
    {
        foreach ($arr as $key => $value) {
            $path_cur = $path . '.' . $key;
            if (is_array($value)) {
                $this->mergeConfigRecursion($value, $path_cur);
                continue;
            }
            $config = $this->app->make('config');

            $config->set($path_cur, $value);
        }
    }
}
