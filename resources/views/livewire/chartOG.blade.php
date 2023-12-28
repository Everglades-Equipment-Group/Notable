<div>
    <div id="chartContainer" class="w-full h-72 my-10"></div>
    @assets
    <script src="https://canvasjs.com/assets/script/canvasjs.min.js"></script>
    @endassets
    @script
        <script>
        document.addEventListener('livewire:initialized', () => {
            console.log($wire.chartType);
            let chart = new CanvasJS.Chart("chartContainer", {
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
                    type: "{{ $chartType }}" ,
                    dataPoints: @json($chartData)
                }
                ]
            });
            chart.render();
        });
        </script>
    @endscript
</div>
<livewire:chart
            wire:key="{{ $this->chartType }}-{{ $this->chartData->count() }}"
            :chartType="$this->chartType"
            :chartData="$this->chartData"
        />