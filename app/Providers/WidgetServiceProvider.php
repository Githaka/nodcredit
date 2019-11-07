<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class WidgetServiceProvider extends ServiceProvider
{

    /**
     * @return void
     */
    public function boot()
    {

        Blade::directive('widget', function ($component) {
            return "<?php echo (app($component))->toHtml(); ?>";
        });

    }
}
