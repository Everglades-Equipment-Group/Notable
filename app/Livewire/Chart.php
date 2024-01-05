<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\Js;
use Livewire\Attributes\Reactive;

class Chart extends Component
{
    public $chartData;
    public $chartType;
    public $xAxisFormat;

    #[Js]
    public function makeChart()
    {
        // return <<<'JS'
        $this->js('
            let data = [];
            Object.entries($wire.chartData).forEach((item, i) => {
                let points = [];
                item[1]["data"].forEach((set) => {
                    points.push({
                        x: new Date(set["x"]),
                        y: set["y"]
                    });
                });
                data.push({
                    showInLegend: true,
                    legendText: item[1]["title"] + " (" + item[1]["total"] + ")",
                    type: $wire.chartType,
                    dataPoints: points
                });
            });
            const chart = new CanvasJS.Chart("chartContainer", {
                animationEnabled: true,
                theme: "light2",
                backgroundColor: "transparent",
                legend:{
                    fontSize: 15,
                    fontColor: "white"      
                },
                axisX:{
                    labelFontColor: "white",
                    labelFontSize: 12,
                    valueFormatString: $wire.xAxisFormat,
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
        return view('livewire.chart');
    }
}
