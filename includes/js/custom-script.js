function selectstory(){
	bookslist.style.display = 'none';
	inputtypeselectfic.style.display = 'none';
	frontendform.style.display = 'block';
	chapternumberdesc.style.display = 'none';
	chapternumber.style.display = 'none';
	postingtitlestory.style.display = 'block';
	postingtitlechapter.style.display = 'none';
	storynumberdesc.style.display='block';
	}
function selectbook(){
	bookslist.style.display = 'block';
	inputtypeselectfic.style.display = 'none';
	frontendform.style.display = 'none';
	chapternumberdesc.style.display = 'block';
	chapternumber.style.display = 'block';
	postingtitlestory.style.display = 'none';
	postingtitlechapter.style.display = 'block';
	storynumberdesc.style.display='none';
	}
function radiobookname(bookname){
	frontendform.style.display = 'block';
	document.getElementById('book_name_input').value = bookname;
	bookslist.style.display = 'none';
	}
function radiogenre(genrename){
	document.getElementById('genre_name_input').value = genrename;
	}
function radiopairing(pairingname){
	document.getElementById('pairing_name_input').value = pairingname;
	}
function radiorating(ratingname){
	document.getElementById('rating_name_input').value = ratingname;
	}
function radiostorycat(storycatname){
	document.getElementById('storycat_name_input').value = storycatname;
	}
function jfandomfilter(letter){
	jQuery("#filteringtabs>div").removeClass("filtertabsselect");
	jQuery("#filtertab"+letter).addClass("filtertabsselect");
	jQuery("#fandoms>div").removeClass("activefandom");
	jQuery("#filterfor"+letter).addClass("activefandom");
}
function cjfandomfilter(letter){
	jQuery("#cfilteringtabs>div").removeClass("filtertabsselect");
	jQuery("#cfiltertab"+letter).addClass("filtertabsselect");
	jQuery("#cfandoms>div").removeClass("activefandom");
	jQuery("#cfilterfor"+letter).addClass("activefandom");
}
