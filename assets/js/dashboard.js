function borrowed(){
  const borrowedButton = document.querySelector('.borrowed');

  if(borrowedButton.style.display === 'none'){
      borrowedButton.style.display = 'block';
  } else{
      borrowedButton .style.display = 'none'
  }
}

function return1(){
  const returnButton = document.querySelector('.return');

  if(returnButton.style.display === 'none'){
      returnButton.style.display = 'block';
  } else{
      returnButton .style.display = 'none'
  }
}

function available(){
  const availableButton = document.querySelector('.available');

  if(availableButton.style.display === 'none'){
    availableButton.style.display = 'block';
  } else{
    availableButton .style.display = 'none'
  }
}

function lost(){
  const lostButton = document.querySelector('.lost');

  if(lostButton.style.display === 'none'){
    lostButton.style.display = 'block';
  } else{
    lostButton .style.display = 'none'
  }
}

function damage(){
  const damageButton = document.querySelector('.damage');

  if(damageButton.style.display === 'none'){
    damageButton.style.display = 'block';
  } else{
    damageButton .style.display = 'none'
  }
}

function replace1(){
  const replaceButton = document.querySelector('.replace');

  if(replaceButton.style.display === 'none'){
    replaceButton.style.display = 'block';
  } else{
    replaceButton .style.display = 'none'
  }
}

function added(){
  const addedButton = document.querySelector('.added');

  if(addedButton.style.display === 'none'){
    addedButton.style.display = 'block';
  } else{
    addedButton .style.display = 'none'
  }
}

function overdue(){
  const overdueButton = document.querySelector('.overdue');

  if(overdueButton.style.display === 'none'){
    overdueButton.style.display = 'block';
  } else{
    overdueButton .style.display = 'none'
  }
}