/**
 * Created by lx on 2016/12/8.
 */
$(function () {
  //封装切图函数
  $.fn.switch = function () {
    $(this).on('click', 'li', function () {
      $(this).addClass('current').siblings('.current').removeClass();
    })
    return $(this);
  };
  //左侧切图
  $('.subSideList-2').switch();
  $('.subSideList').switch();
  $('.chkStyle').click(function () {
    $(this).toggleClass('backgroundshow');
  });

  $('#kind').change(function () {
    var value = $("#kind").val();
    if (value == 2) {
      $('#style').css('display', "none");
    } else {
      $('#style').css('display', "inline-block");
    }
  });

/*
  var categories = [
    {'id': 10, "name": "住宅空间", "children": [
      {'id': 101, 'name': '家装', 'children': [
        {'id': 1011, 'name': '小户型'}
      ]},
      {'id': 102, 'name': '豪华别墅', 'children': [
        {'id': 1021, 'name': '别墅设计'},
        {'id': 1022, 'name': '豪宅设计'}
      ]}
    ]},
    {'id': 20, 'name': "公共空间", "children": [
      {'id':201,'name':"酒店空间",'children':[{
        'id':2011,'name':'商务酒店设计'}
      ]}
    ]}
  ];
*/

// 本地图片预览
/*  $('.imgInnerBox').click(function(){
    //$('#upload').click();
    $('#upload').on('change',function(){
      var objUrl=getObjectURL(this.files[0]);
      if (objUrl){
        $('.selImg').attr('src',objUrl);
      }
    });
  });
  function getObjectURL(file){
    var url=null;
    if (window.createObjectURL!=undefined){
      url=window.createObjectURL(file);
    }else if (window.URL != undefined) {
      // webkit or chrome
      url = window.URL.createObjectURL(file);
    }
    return url;
  }*/


})
