<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Js;
use Livewire\Attributes\Reactive;

class Chart extends Component
{
    // #[Reactive]
    public $chartData;
    public $chartType;

    #[Js]
    public function makeChart()
    {
        // return <<<'JS'
        $this->js('
            const chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                theme: "light2",
                backgroundColor: "transparent",
                axisX:{
                    labelFontColor: "white",
                    labelFontSize: 10,
                },
                axisY:{
                    labelFontColor: "white",
                    labelFontSize: 14,
                },
                data: [              
                {
                    type: $wire.chartType ,
                    dataPoints: $wire.chartData
                }
                ]
            });
            chart.render();
        ');
        // JS;
    }

    public function mount($chartType, $chartData)
    {
        // $this->chartType = $chartType;
        // $this->chartData = $chartData;
        $this->makeChart();
    }


    public function render()
    {
            // $this->js('
            //     const chart = new CanvasJS.Chart("chartContainer", {
            //         animationEnabled: true,
            //         theme: "light2",
            //         backgroundColor: "transparent",
            //         axisX:{
            //             labelFontColor: "white",
            //             labelFontSize: 10,
            //         },
            //         axisY:{
            //             labelFontColor: "white",
            //             labelFontSize: 14,
            //         },
            //         data: [              
            //         {
            //             type: $wire.chartType ,
            //             dataPoints: $wire.chartData
            //         }
            //         ]
            //     });
            //     chart.render();
            // ');
        return view('livewire.chart', [
            // 'chartType' => $this->chartType,
            // 'chartData' => $this->chartData,
        ]);
    }
}
