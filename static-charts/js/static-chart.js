function drawStaticChart(opts){
    var ctx = document.getElementById(opts.canvasId).getContext('2d');
    // Fetch data via REST API
    Promise.all(opts.indicators.map(function(slug){
        return fetch(window.location.origin + '/wp-json/zc-dmt/v1/data/' + slug).then(r=>r.json());
    })).then(resArr=>{
        var labels=resArr[0].labels; var datasets=[];
        resArr.forEach(function(res,i){ datasets.push({label:res.indicator,data:res.data}); });
        new Chart(ctx,{type:opts.type,data:{labels:labels,datasets:datasets}});
    }).catch(function(){ console.log('static chart error'); });
}