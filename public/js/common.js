function getData(url, data, append = {append: false}, parse = false) {
  let val;
  try {
    $.ajax({
      type: 'POST',
      getType: 'json',
      url: url,
      async: false,
      data: data,
      success: function(res) {
        // if ( parse ) val = JSON.parse(res);
        // else val = res;
        val = res;
      },
      error: function(XMLHttpRequest, textStatus, errorThrow) {
        console.log("error XMLHttpRequest", XMLHttpRequest, ' textStatus ', textStatus);
        val = XMLHttpRequest.responseText;
        // return false;
      }
    });
  } catch(e) {
    val = e;
  } finally {  
    if ( parse ) val = JSON.parse(val);
    if ( append.append ) {
      appendData(append.target, val, append.init);
    }
  }

  return val;
}

function appendData($target, result, init = false) {
  if ( $target == '' || $target == null || typeof $target !== 'object') return;
  
  if ( init ) {
    if ( $target.children().length > 0 || $target.length > 0 ) $target.empty();
  }

  if ( result != '' ) $target.append(result);
  else $target.attr('display', '');
}

function convertData(tag) {
  let _return;
  switch (tag) {
    case 'select':
      _return = $("<option>");
      break
    default : 
      _return =  $("<li/>");
      break
  }

  return _return;
}

function activeToggle(target) {
  if ( target.hasClass('active') ) {
    target.removeClass('active');
  } else target.addClass('active');
}

// function onKeyupEvt() {}

$(function() {
  $.numberWithCommas = function (x) {
	  return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ',');
	}

  $.withoutCommas = function (x) {
    return x.toString().replace(',', '');
  }
});

$(document).ready(function() {
  if ( $('input[type="checkbox"].value-change, input[type="radio"].value-change').length ) {
    Array.from($('input[type="checkbox"].value-change, input[type="radio"].value-change')).forEach(element => {
      if ( $(element).is(':checked') === false ) {
        $(element).val(0);
      }
    });
  }
}).on('change', 'input[type="checkbox"].value-change, input[type="radio"].value-change', function() {
  let type = $(this).attr('type');  
  $(this).val(Number($(this).is(':checked')));

  if ( typeof $(this).data('findTarget') != 'undefined' ) {
    let findTarget = $(this).data('findTarget');
    let condition = $(this).data('condition');
    let prop = {};
    let $target = '', $targetParent = '';

    $targetParent = findParentInit($(this));
    $target = $targetParent.find(findTarget);
    // console.log("$targetParent ", $targetParent , ' $target ', $target);
    
    if ( type == 'radio' ) radioTypeValueInit($(this).attr('name'), condition);

    if ( condition.length > 0 ) {
      $.each(condition, (i, v) => {
        if ( v['condition'] === $(this).val() ) {
          prop[v['action']] = v['value'];
        }
      });
    }
    $target.prop(prop);
  }
});

function findParentInit($this) {
  let $parent = $this.data('findParent');
  let $closest = $this.data('findClosest');
  let targetParent;

  if ( $parent == '' ) {
    if ( $closest != 'undefined' ) targetParent = $this.closest($closest);
  } else {
    targetParent = $this.parent($parent);
    if ( targetParent.length == 0 ) {
      targetParent = $this.parents($parent);
    }
  }
  return targetParent;
}

function radioTypeValueInit(name, condition) {
  let prop = {};
  $.each( $(`[name="${name}"]`), (idx, ele) => {
    if ( ele.checked === false ) {
      let $parents = findParentInit($(ele));
      let $target = $(ele).data('findTarget');
      $(ele).val(Number($(this).is(':checked')));

      if ( condition.length > 0 ) {
        $.each(condition, (i, v) => {
          if ( v['condition'] === $(ele).val() ) {
            prop[v['action']] = v['value'];
          }
        });
      }
      $parents.find($target).prop(prop);
    }
  })
}