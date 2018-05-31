export default {

    // 防抖函数
    // fn: 要被防抖的函数
    // delay: 延迟时长
    // mustRunDelay: 必须运行的延迟时长
    throttle: function (fn, delay, mustRunDelay) {
        var timer = null;
        var t_start;
        return function () {
            var context = this, args = arguments, t_curr = +new Date();
            clearTimeout(timer);
            if (!t_start) {
                t_start = t_curr;
            }
            if (t_curr - t_start >= mustRunDelay) {
                fn.apply(context, args);
                t_start = t_curr;
            }
            else {
                timer = setTimeout(function () {
                    fn.apply(context, args);
                }, delay);
            }
        };
    },
    // 求一个数组的最大值
    max: function (arr) {
        let max = arr[0];
        for (let item of arr) {
            if (parseInt(max) < parseInt(item)) {
                max = item;
            }
        }
        return max;
    },
    // 求一个数组的最小值
    min: function (arr) {
        let min = arr[0];
        for (let item of arr) {
            if (parseInt(min) > parseInt(item)) {
                min = item;
            }
        }
        return min;
    },
    // 求一个数组的平均值
    average: function(arr){
        let sum = 0;
        for(let item of arr){
            sum += parseInt(item);
        }
        return sum/arr.length;
    },
    // 生成由 start 到 end 构成的数组
    rangeArray:(start, end) => Array(end - start + 1).fill(0).map((v, i) => i + start)
}