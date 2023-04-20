$(document).ready(function() {
  let temp = [1, 2, 3, 4, 5, 6, 7, 8, 9];  
  // console.log("length ", temp.length);
  
  for(let i = 1; i < temp.length; i++ ) {
    for( let j = 0; j < temp.length; j++ ) {
      console.log(temp[i] + " * " + temp[j] + " = " + (temp[i] * temp[j]));
    }
  }

  $.each(temp, function(index, value) {
    if ( index > 0 ) {
      $.each(temp, (index, value2) => {
        console.log(value + " * " + value2 + " = " + (value*value2));
      }); 
      if ( index > 8 ) console.log("띄여쓰기");
    }
  });
  
  temp.forEach(function(value, index) {
    if ( index > 0 ) {
      temp.forEach(element => {
        console.log(value + " * " + element + " = " + (value * element));
      });
      if ( index < (temp.length -1) ) console.log("띄어쓰기");
    }
  });
});