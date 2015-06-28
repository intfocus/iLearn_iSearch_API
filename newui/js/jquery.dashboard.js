/**
* Theme: Velonic Admin Template
* Author: Coderthemes
* Module/App: Dashboard Application
*/

!function($) {
    "use strict";

    var Dashboard = function() {
        this.$body = $("body")
    };

    //initializing various charts and components
    Dashboard.prototype.init = function() {
        /**
        * Morris charts
        */

        //Line chart
        Morris.Line({
            element: 'morris-line-example',
            data: [
                { y: '1', a: 75},
                { y: '2', a: 50},
                { y: '3', a: 75},
                { y: '4', a: 75},
                { y: '5', a: 100}
            ],
            xkey: 'y',
            ykeys: ['a'],
            labels: ['实际拜访'],
            resize: true,
            lineColors: ['#1a2942', '#3bc0c3']
        });

        //Bar chart
        Morris.Bar({
            element: 'morris-bar-example',
            data: [
                    { y: '201411', a: 75,  b: 65 , c: 20 },
                    { y: '201412', a: 50,  b: 40 , c: 50 },
                    { y: '201501', a: 75,  b: 65 , c: 95 },
                    { y: '201502', a: 50,  b: 40 , c: 22 },
                    { y: '201503', a: 75,  b: 65 , c: 56 },
                    { y: '201504', a: 100, b: 90 , c: 60 },
                    { y: '201505', a: 100, b: 90 , c: 60 }
            ],
            xkey: 'y',
            ykeys: ['a', 'b', 'c'],
            labels: ['目标', '实际', '预测'],
            barColors: ['#3bc0c3', '#1a2942', '#dcdcdc']
        });


        //Chat application -> You can initialize/add chat application in any page.
        $.ChatApp.init();
    },
    //init dashboard
    $.Dashboard = new Dashboard, $.Dashboard.Constructor = Dashboard
    
}(window.jQuery),

//initializing dashboad
function($) {
    "use strict";
    $.Dashboard.init()
}(window.jQuery);



