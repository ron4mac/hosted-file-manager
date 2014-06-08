var jqCropper = {
	load: function(id,src) {
		//alert(id+" : "+src);
		$(id).load("jqcroped.php", {'src':src}, function(){
				var eelm = $(id);
				var ewid = eelm.width();
				eelm.css({"margin-left":"-"+(ewid/2)+"px"});
				eelm.show(); 
			});
	}
};

function edFile (e,elem)
{
	$(e).stop();
	//console.log(e,elem);
	//alert(e.srcElement.href);
	jqCropper.load("#editDiv", e.srcElement.href);
}

function kcRotate (elem, deg)
{
	var Dx;
	var Dy;
	var iecos;
	var iesin;
	var halfWidth;
	var halfHeight;

	//degrees to radians
	var rad=deg*(Math.PI/180);

	//get sine and cosine of rotation angle
	iecos=Math.cos(rad);
	iesin=Math.sin(rad);

	//get element's size
	halfWidth=elem.offsetWidth/2;
	halfHeight=elem.offsetHeight/2;

	//calculating position correction values
	Dx=-halfWidth*iecos + halfHeight*iesin + halfWidth;
	Dy=-halfWidth*iesin - halfHeight*iecos + halfHeight;

	//applying CSS3 rotation
	elem.style.transform="rotate("+rad+"rad)";

	//vendor prefixed rotations
	elem.style.mozTransform="rotate("+rad+"rad)";
	elem.style.webkitTransform="rotate("+rad+"rad)";
	elem.style.OTransform="rotate("+rad+"rad)";
	elem.style.msTransform="rotate("+rad+"rad)";

	//rotation Matrix for IExplorer
	if(navigator.appVersion.toLowerCase().match("msie 8.0"))
	elem.style.filter="progid:DXImageTransform.Microsoft.Matrix(M11="+iecos+", M12="+-iesin+", M21="+iesin+", M22="+iecos+", Dx="+Dx+", Dy="+Dy+", SizingMethod=auto expand)";
	elem.style.msFilter="progid:DXImageTransform.Microsoft.Matrix(M11="+iecos+", M12="+-iesin+", M21="+iesin+", M22="+iecos+", Dx="+Dx+", Dy="+Dy+", SizingMethod=auto expand)";
}
