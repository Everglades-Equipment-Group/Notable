<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Js;
use Livewire\Attributes\Reactive;

class Chart extends Component
{
    public $chartData;
    public $chartType;

    #[Js]
    public function makeChart()
    {
        // return <<<'JS'
        $this->js('
            console.log(Object.entries($wire.chartData));
            let data = [];
            Object.entries($wire.chartData).forEach((item, i) => {
                console.log(item);
                data.push({
                    showInLegend: true,
                    legendText: item[1]["title"],
                    type: $wire.chartType ,
                    dataPoints: item[1]["data"]
                });
            });
            // console.log(data);
            const chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                theme: "light2",
                backgroundColor: "transparent",
                axisX:{
                    labelFontColor: "white",
                    labelFontSize: 12,
                },
                axisY:{
                    labelFontColor: "white",
                    labelFontSize: 14,
                },
                data: data        
                
            });
            chart.render();
        ');
        // JS;
    }

    public function mount($chartType, $chartData)
    {
        $this->makeChart();
    }


    public function render()
    {
        return view('livewire.chart', [

        ]);
    }
}
