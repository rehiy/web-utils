window.onload = function() {
    var winHeight = document.documentElement.clientHeight;
    document.getElementsByTagName('body')[0].style.height = winHeight+'px';

    var $clock = document.getElementById('clock'),
        $date = document.getElementById('date'),
        $hour = document.getElementById('hour'),
        $min = document.getElementById('min'),
        $sec = document.getElementById('sec'),
        oSecs = document.createElement('em');
    for (var i = 1; i < 61; i++) {
        var tempSecs = oSecs.cloneNode(),
        pos = getSecPos(i);
        if(i%5==0){
            tempSecs.className = 'ishour';
            tempSecs.innerHTML = '<i style="-webkit-transform:rotate('+(-i*6)+'deg);">'+(i/5)+'</i>';
        }
        tempSecs.style.cssText='left:'+pos.x+'px; top:'+pos.y+'px; -webkit-transform:rotate('+i*6+'deg);';
        $clock.appendChild(tempSecs);
    }

    // 圆弧上的坐标换算
    function getSecPos(dep) {
        var hudu = (2*Math.PI/360)*6*dep,
        r = 200; //半径大小
        return {
            x: Math.floor(r + Math.sin(hudu)*r),
            y: Math.floor(r - Math.cos(hudu)*r),
        }
    }


    ;(function() {
        // 当前时间
        var _now = new Date(),
        _h = _now.getHours(),
        _m = _now.getMinutes(),
        _s = _now.getSeconds();

        var _day = _now.getDay(),
        weekAry = ['七','一','二','三','四','五','六'];


        $date.innerHTML = _now.getFullYear()+'-'+(_now.getMonth()+1)+'-'+_now.getDate()+' 星期'+weekAry[_day];
        //绘制时钟
        var gotime = function(){
            var _h_dep = 0;
            _s++;
            if(_s>59){
                _s=0;
                _m++;
            }
            if(_m>59){
                _m=0;
                _h++;
            }
            if(_h>12){
               _h = _h-12;
            }

            //时针偏移距离
            _h_dep = Math.floor(_m/12)*6; 

            $hour.style.cssText = '-webkit-transform:rotate('+(_h*30-90+_h_dep)+'deg);';
            $min.style.cssText = '-webkit-transform:rotate('+(_m*6-90)+'deg);';
            $sec.style.cssText = '-webkit-transform:rotate('+(_s*6-90)+'deg);';

        };

        gotime();
        setInterval(gotime, 1000);
    })();

};