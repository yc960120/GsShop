$(function(){
//点击增加按钮触发事件
    $("#addgoods").click(function(){
        var num = $(this).parent().children("span");
//单品数量增加
        num.text(parseInt(num.text())+1);
//商品总数增加
        var totalNum = parseInt($(".J_SelectedItemsCount").text());
        totalNum++;
        $(".J_SelectedItemsCount").text(totalNum);
//计算总价
        var goods_price = parseInt($(this).parents(".td td-amount").prev().children(".item-price price-promo-promo").children(".price-content").children("price-line").children("#danjia").text());
        $(".J_ItemSum number").text(parseInt($(".J_ItemSum number").text())+goods_price);
    });

//点击减少按钮触发事件
     $("#minus").click(function(){
        var num = $(this).parent().children("span");
        if(parseInt(num.text())){
        num.text(parseInt(num.text())-1);
        var totalNum = parseInt($(".J_SelectedItemsCount").text());
        totalNum--;
        $(".J_SelectedItemsCount").text(totalNum);
        var goods_price = parseInt($(this).parent().parent().parent().parent().parent().children(".J_ItemSum number").text());
        $(".J_ItemSum number").text(parseInt($(".J_ItemSum number").text())-goods_price);
    } else{
        num.text("0");
    }
    });
 });

