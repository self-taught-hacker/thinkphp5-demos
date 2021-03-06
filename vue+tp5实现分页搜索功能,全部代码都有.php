网上转一大圈,心灰意冷,不得不吐槽下,你分享个代码要分享就分享完吧,拿一部分,我这种前端菜鸡,看什么.  算了求人不如求己,自己丰衣足食吧,

研究一下午总算搞出来了,当然少不了公司大佬从中作梗.

直接上全部代码:

代码高亮有点问题,看不清楚的直接复制到自己IDE上慢慢研究吧  , 精力有限 .  也给自己做个记录,下次就直接搬砖

页面部分:


<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title></title>
        {css href="/static/admin/css/bootstrap.min.css" /}
        {js href="/static/admin/js/jquery-3.2.1.min.js" /}
        {js href="/static/admin/layer/layer.js" /}
        {js href="/static/admin/js/vueJs/vue.min.js" /}
        <style type="text/css">

        </style>
    </head>
<body>
<ul class="breadcrumb">
    <li><span href="#">位置</span></li>
    <li><a href="#">输赢报表</a></li>
</ul>
<div id="app">
        <form class="form-inline">
            <div class="form-group">
                <label>时间搜索</label>
                <input type="date" class="form-control" v-model="start_time"> --
                <input type="date" class="form-control" v-model="end_time">
            </div>
            <div class="form-group">
                <input type="text" v-model="name" class="form-control" id="exampleInputName2" placeholder="会员名称">
            </div>
            <div class="form-group">
                <input type="text" v-model="agent" class="form-control" id="exampleInputName3" placeholder="代理名称">
            </div>
            <div class="form-group" v-if="apiconfigx.length">
                <select class="form-control"   v-model="type">
                    <option value="-1">选择运行商</option>
                    <option  :value="coupon.id" v-for="coupon in apiconfigx" >{{coupon.name}}</option>
                </select>
            </div>
            <div class="form-group" v-if="type !=-1">
                <select class="form-control"   v-model="type_yx" >
                    <option value="-1">游戏类型</option>
                    <option  :value="coupon.king_id" v-for="coupon in couponList" >{{coupon.name}}</option>
                </select>
            </div>
            <div class="form-group">
                <input type="text" v-model="number" class="form-control" id="exampleInputName4" placeholder="局号">
            </div>
            <button type="button" class="btn btn-success" @click="Searchx()" >搜索</button>
            <button type="button" @click="reLoad()" class="btn btn-default">刷新</button>
        </form>
    <br>
    <div class="container">
        <p class="text-right" style="color: red">{{tj}}</p>
    </div>

        <br>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>开始时间</th>
                    <th>结束时间</th>
                    <th>用户名</th>
                    <th>游戏类型</th>
                    <th>房间类型</th>
                    <th>总投注</th>
                    <th>有效投注额</th>
                    <th>输赢金额</th>
                    <th>局号</th>
                    <th>游戏结果</th>
                    <th>运行商</th>
                </tr>
            </thead>
            <tbody>
                <template  v-if="data_list.length"  v-for="(vo,key) in data_list">
                    <tr>
                        <td>{{vo.game_start_time}}</td>
                        <td>{{vo.game_end_time}}</td>
                        <td>{{vo.username}}</td>
                        <td>{{vo.GameType}}</td>
                        <td>{{vo.server_id}}</td>
                        <td>{{vo.all_bet}}</td>
                        <td>{{vo.cell_score}}</td>
                        <td>{{vo.profit}}</td>
                        <td>{{vo.game_id}}</td>
                        <td><a href="#" @click="yxjg(vo.kind_id,vo.card_value)">点击查看</a>{{vo.kind_id}}</td>
                        <td>{{vo.Operator}}</td>
                    </tr>
                </template>
                <template  v-if="data_list==''">
                    <tr>
                        <td colspan="11" style="text-align: center;">没有找到匹配的记录</td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
    <div class="pagin"  v-if="data_list.length" >
        <div class="message" style="float:left">共<i class="blue"> {{all_list}}</i>条记录，共&nbsp;<i class="blue"> {{all_page}}</i>&nbsp;页，当前显示第&nbsp;<i class="blue"> {{page}}&nbsp;</i>页</div>
        <ul v-if="all_page > 1" style="float:right" class="pagination">
            <li><a v-if="pageList[0] == '...'" @click="pageNext(1)" class="syy">首页</a></li>
            <li><a @click="pageNext('prve')" class="syy">上一页</a></li>
            <li v-for="vo in pageList" @click="pageNext(vo)" :class="['syy_list',{'active':vo == page}]"><a >{{vo}}</a></li>
            <li><a @click="pageNext('next')" class="syy"> 下一页 </a></li>
            <li><a v-if="pageList[pageList.length-1] == '...'" @click="pageNext(all_page)" class="syy"> 尾页 </a></li>
        </ul>
    </div>
    <div id="yxjga" style="display: none">
        <div class="row">
            <div class="col-sm-12">
                <p>{{yxjgx}}</p>
            </div>
        </div>
    </div>
</div>

<script>
    var vm = new Vue({
        el:'#app',
        mounted:function(){ //初始化调用
            this.apiconfig();
            this.getData();
        },

        // 我们的数据对象
        data:{
            apiconfigx:[], //运行商
            couponList:[], //游戏分类
            data_list:[], //请求返回的数据
            //-----------分页----------
            page:1, //当前页
            all_page:1,//总页数
            all_list:0,//总数据条数
            //---------搜索条件--------
            name:'',//用户名称
            agent:'',//用户名称
            start_time:'',//开始时间
            end_time:'',//结束时间
            type:-1, //运行商
            type_yx:-1, //游戏类型
            number:'', //局号
            search:0,// 判断是否点击搜索
            tj:'', //总统计
            yxjgx:'', //游戏结果
        },
        watch:{
            type:function(){
                this.couponListlei();
            }
        },
        //自动加载
        computed:{

            //首页 和尾页
            pageList:function(){
                var _this = this;
                if(_this.all_page <= 10){
                    return _this.all_page;
                }else{
                    let bit = [_this.page],rs = _this.page;
                    for(let i=1;i<=5;i++ ){
                        if(parseInt(rs) - parseInt(i) <= 0){
                            bit.push(parseInt(bit[ bit.length-1 ]) + 1);
                        }else{
                            bit.unshift( parseInt(bit[0]) - 1 );
                        }
                    }
                    for( let i=1;i<=4;i++ ){
                        if(parseInt(rs) + parseInt(i) <= _this.all_page){
                            bit.push(parseInt(bit[ bit.length-1 ]) + 1);
                        }else{
                            bit.unshift( parseInt(bit[0]) - 1 );
                        }
                    }
                    if(bit[bit.length - 1] != _this.all_page){
                        bit.push('...');
                    }
                    if(bit[0] != 1){
                        bit.unshift('...');
                    }
                    return bit;
                }
            }
        },
        methods:{
            yxjg:function (id,varl) {
                var _this = this;
                $.post('/djycpgk/wqxfw/cardvalue',{type:id,val:varl},function(res){
                    //console.log(res);
                    _this.yxjgx = res;
                    _this.$nextTick(function(){
                        layer.open({
                            type: 1,
                            skin: 'layui-layer-rim', //加上边框
                            area: ['420px', '240px'], //宽高
                            content:  $('#yxjga').html(),
                        });
                    });
                });




            },
            //搜索
            Searchx:function () {
                var _this = this;
                if(_this.name ==''  && _this.type=='' && _this.start_time=='' && _this.end_time==''&& _this.agent)
                {
                    _this.search = 0;
                }else {
                    _this.search = 1;
                }
                _this.page =  1;
                _this.getData();
            },
            //运营商
            apiconfig:function () {
                var _this = this;
                $.post('/djycpgk/Chess_game/apiconfig',{},function(res){
                    _this.$set(_this,'apiconfigx',res);
                });
            },
            //游戏分类
            couponListlei:function () {
                var _this = this;
                if(_this.type == -1){
                    _this.couponList=[];
                    return;
                }
                $.post('/djycpgk/Chess_game/couponList',{type:_this.type},function(res){
                    _this.$set(_this,'couponList',res);
                });
            },
            getData:function () {
                // layer.load(2);//开启加载动画
                var _this = this;
                var data_list = {
                    page: _this.page,
                    name: _this.name,
                    agent: _this.agent,
                    start_time: _this.start_time,
                    end_time: _this.end_time,
                    type: _this.type,
                    type_yx: _this.type_yx,
                    number: _this.number,
                    search: _this.search,
                }
                $.post('/djycpgk/Chess_game/reportForm',data_list,function(res){
                    layer.closeAll('loading');//关闭加载动画
                    if(res[0].data){
                        _this.page = res[0].current_page;
                        _this.all_page = res[0].last_page;
                        _this.all_list = res[0].total;
                        _this.$set(_this,'data_list',res[0].data);
                        _this.tj = res[1];
                    }
                });
            },
            //下一页 上一页
            pageNext:function(type){
                if(!type){return false;}
                var _this = this;
                if(!isNaN(type)){
                    _this.page = type;
                }else{
                    if(type == 'prve'){
                        _this.page -= 1;
                    }else if(type == 'next'){
                        _this.page = parseInt(_this.page) + 1;
                    }else{
                        return false;
                    }
                    if(_this.page < 1 ){
                        _this.page = 1;
                        return false;
                    }else if(_this.page > _this.all_page){
                        _this.page = _this.all_page;
                        return false;
                    }
                }
                _this.getData();
            },
            reLoad:function(){
                let _this = this;
                _this.page = 1,
                _this.name = '',
                _this.user_id = '',
                _this.agent = '',
                _this.start_time = '';
                _this.end_time = '';
                _this.type = -1;
                _this.type_yx = -1;
                _this.number = '';
                _this.search = 0;
                _this.getData();

            }


        },

    });

</script>
</body>
</html>
后端部分:


public function adminData($start_time = '', $end_time = '', $username = '', $search = 0)
    {

        $Admin = new \app\index\model\Admin();
        if ($search == 1) {
            $where = [];
            if ($username != '') {
                $where[] = ['username', '=', trim($username)];
            }
            if ($start_time != '' && $end_time == '') {
                $where[] = ['workdate', '>=', $start_time];
            }
            if ($end_time != '' && $start_time == '') {
                $where[] = ['workdate', '<=', $end_time];
            }
            if ($end_time != '' && $start_time != '') {
                $Admin->whereBetweenTime('workdate', $start_time, $end_time);
                $where[] = ['workdate', 'between time', [$start_time, $end_time]];
            }
            $data = $Admin->where($where)->paginate(config('page_num'), false, ['query' => $_GET]);
            $page = $data->render();
            return json([
                'data' => $data,
                'page' => $page
            ]);
        }
        $data = $Admin->paginate(config('page_num'), false, ['query' => $_GET])->each(function($item, $key){
            $item->workdate= dateFormat($item->workdate);
            $item->quitdate= dateFormat($item->quitdate);
            $item->groupid= \app\index\model\AuthGroup::getGroupIdTitle($item->groupid);
        });
        $page = $data->render();
        return json([
            'data' => $data,
            'page' => $page
        ]);
    }
tp5自带render就不用后端再去写分页了 直接拿来用就可以
