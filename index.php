<?php
$link = "http" . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == "on") ? "s" : "") . "://";
$server = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : $_SERVER['SERVER_NAME'];
$config['base_url'] = $link . $server;
//$config['base_url'] .= dirname($_SERVER['SCRIPT_NAME']).'/';
$config['base_url'] .= preg_replace('@/+$@', '', dirname($_SERVER['SCRIPT_NAME'])) . '/';
$base_url = str_replace('servicepdf/', '', $config['base_url']);
?>

<!doctype html>
<html>

	<head>
		<meta charset="utf-8">
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Web Service PDF to PNG</title>

		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	</head>

	<body>
		<?php
			$val_id = $_GET['doc'];
			$my_UNIQID = uniqid("sisumaker_");
		?>

		<form action="imagewrite.php" method="post">
			<div align="center">
				<canvas id="the-canvas" style="border:2px solid;"></canvas>
				<br/>
				<span>
					<button type="button" style="font-size:24px" id="prev_btn" onclick="changePage('prev')"><i class="fa fa-arrow-circle-o-left"></i> PREV</button>
					<input type="number" id="curr_page" style="text-align:center;" disabled>
					<button type="button" style="font-size:24px" id="next_btn" onclick="changePage('next')">NEXT <i class="fa fa-arrow-circle-o-right"></i></button>
				</span>
			</div>

			<div>
				<div>
					<input type="hidden" id="x1" disabled>
					<input type="hidden" id="y1" disabled>
					<input type="hidden" id="x2" disabled>
					<input type="hidden" id="y2" disabled>
				</div>
				<div>
					<input type="hidden" id="kotak_left" disabled>
					<input type="hidden" id="kotak_top" disabled>
					<input type="hidden" id="kotak_right" disabled>
					<input type="hidden" id="kotak_bottom" disabled>
				</div>
				<div>
					<input type="text" name="position[]" id="llx" readonly>
					<input type="text" name="position[]" id="lly" readonly>
					<input type="text" name="position[]" id="urx" readonly>
					<input type="text" name="position[]" id="ury" readonly>
				</div>
				<div>
					<input type="hidden" id="nomor_cantik" readonly>
				</div>
			</div>

			<input type="submit" value="Submit">

		</form>
		

		<script>
			var canvas_global, ctx_global, flag = false,
            prevX = 0,
            currX = 0,
            prevY = 0,
            currY = 0,
            baseX = 0,
            baseY = 0,
            w, llx, lly, urx, ury,
			point_x1, point_y1, point_x2, point_y2,
            h, dvcRatio;
			scale = 1.0;
			dot_flag = false;
			first_pinch = true;
			first_init = true;
			var ori_img;

			function init() {
				canvas_global = document.getElementById('the-canvas');
				ctx_global = canvas_global.getContext("2d");
				w = canvas_global.width;
				h = canvas_global.height;

				document.getElementById("nomor_cantik").value = '<?php echo ($my_UNIQID); ?>';

				console.log('Device pixel ratio original : ' + window.devicePixelRatio);
				dvcRatio = Math.round(window.devicePixelRatio);
				console.log('Rounded device pixel ratio : ' + dvcRatio);

				canvas_global.addEventListener("mousemove", function(e) {
					findxy('move', e)
				}, false);
				canvas_global.addEventListener("mousedown", function(e) {
					findxy('down', e)
				}, false);
				canvas_global.addEventListener("mouseup", function(e) {
					findxy('up', e)
				}, false);
				canvas_global.addEventListener("mouseout", function(e) {
					findxy('out', e)
				}, false);

			}

			function drawRect() {
				/*Get viewport by calling getBoundingClientRect() function*/
				var kotak = canvas_global.getBoundingClientRect();
				var lenX = currX - baseX;
				var lenY = currY - baseY;

				document.getElementById("kotak_left").value = kotak.left;
				document.getElementById("kotak_top").value = kotak.top;
				document.getElementById("kotak_right").value = kotak.right;
				document.getElementById("kotak_bottom").value = kotak.bottom;

				var srcX = baseX - kotak.left;
				var srcY = baseY - kotak.top;
				var dstX = srcX + lenX;
				var dstY = srcY + lenY;

				/*Change the coordinate system.
					HTML5 Canvas defines [0,0] coordinate in the upper left corner
				Meanwhile itextPDF defines [0,0] coordinate in the lower left corner*/
				var trans_x1 = srcX;
				var trans_x2 = dstX;
				var trans_y1 = h / dvcRatio - srcY;
				var trans_y2 = h / dvcRatio - dstY;

				llx = (trans_x1 <= trans_x2) ? trans_x1 : trans_x2;
				urx = (trans_x1 > trans_x2) ? trans_x1 : trans_x2;
				lly = (trans_y1 <= trans_y2) ? trans_y1 : trans_y2;
				ury = (trans_y1 > trans_y2) ? trans_y1 : trans_y2;

				//Redraw canvas to its first 'state'
				ctx_global.clearRect(0, 0, w, h);
				ctx_global.putImageData(ori_img, 0, 0);

				//Draw Rectangle above the canvas
				ctx_global.strokeStyle = "blue";
				ctx_global.lineWidth = dvcRatio;
				ctx_global.beginPath();
				ctx_global.rect(srcX * dvcRatio, srcY * dvcRatio, lenX * dvcRatio, lenY * dvcRatio);
				ctx_global.stroke();
				ctx_global.closePath();

				getCoordinate();
			}

			function getCoordinate() {
				document.getElementById("llx").value = llx / scale;
				document.getElementById("lly").value = lly / scale;
				document.getElementById("urx").value = urx / scale;
				document.getElementById("ury").value = ury / scale;
			}

			function color(obj) {
				switch (obj.id) {
					case "green":
                    x = "green";
                    break;
					case "blue":
                    x = "blue";
                    break;
					case "red":
                    x = "red";
                    break;
					case "yellow":
                    x = "yellow";
                    break;
					case "orange":
                    x = "orange";
                    break;
					case "black":
                    x = "black";
                    break;
					case "white":
                    x = "white";
                    break;
				}
				if (x == "white") y = 14;
				else y = 1;
			}

			function findxy(res, e) {
				if (res == 'down') {
					console.log("DOWN action");
					baseX = e.clientX;
					baseY = e.clientY;

					flag = true;

					/*Because the canvas will be redrawn through the mouse-MOVE event,
						we will need to store its initial 'image state'.
					This initial 'image state' later will act as a background of canvas, where we draw rectangle above it.*/
					if (first_init) {
						ori_img = ctx_global.getImageData(0, 0, w, h);
						first_init = false;
					}

					document.getElementById("x1").value = baseX;
					document.getElementById("y1").value = baseY;
				}
				if (res == 'up') {
					console.log("UP action");
					flag = false;
					console.log("Dest X >>>" + e.clientX);
					console.log("Dest Y >>>" + e.clientY);
				}
				if (res == 'out') {
					console.log("OUT action");
					flag = false;
				}
				if (res == 'move') {
					console.log("MOVE action");
					if (flag) {
						prevX = currX;
						prevY = currY;

						currX = e.clientX;
						currY = e.clientY;

						document.getElementById("x2").value = currX;
						document.getElementById("y2").value = currY;

						drawRect();
					}
				}
			}

			var img_list = [];
			var total_page = 0;
			var the_curr_page = 0;
			var hashParams = (function() {
				var search = document.location.search.substring(1);
				var parts = search.split('&');
				var params = {};
				for (var i = 0, l = parts.length; i < l; ++i) {
					var param = parts[i].split('=');
					var key = param[0];
					var value = param.length > 1 ? param[1] : null;
					params[decodeURIComponent(key)] = decodeURIComponent(value);
				}
				return params;
			})();
			var total_memory = ('total_memory' in hashParams) ? parseInt(hashParams['total_memory']) : 1024 * 1024 * 100;
			var Module = {
				TOTAL_MEMORY: total_memory,
				noExitRuntime: true,
				print: function() {
					console.group.apply(console, arguments);
					console.groupEnd();
				},
				printErr: function() {
					console.group.apply(console, arguments);
					console.groupEnd();
				},
				getPDF: function(pathfile) {
					var canvas_list = [];
					var devicePixelRatio = Math.round(window.devicePixelRatio);
					var timer = null;

					var crawp = Module['crap'];
					PDFiumJS.C = {
						init: cwrap('PDFiumJS_init', null, []),
						Doc_new: cwrap('PDFiumJS_Doc_new', 'number', ['number', 'number']),
						Doc_delete: cwrap('PDFiumJS_Doc_delete', null, ['number']),
						Doc_get_page_count: cwrap('PDFiumJS_Doc_get_page_count', 'number', ['number']),
						Doc_get_page: cwrap('PDFiumJS_Doc_get_page', 'number', ['number', 'number']),
						Page_get_width: cwrap('PDFiumJS_Page_get_width', 'number', ['number']),
						Page_get_height: cwrap('PDFiumJS_Page_get_height', 'number', ['number']),
						Page_get_bitmap: cwrap('PDFiumJS_Page_get_bitmap', 'number', ['number']),
						Bitmap_get_buffer: cwrap('PDFiumJS_Bitmap_get_buffer', 'number', ['number']),
						Bitmap_get_stride: cwrap('PDFiumJS_Bitmap_get_stride', 'number', ['number']),
						Bitmap_destroy: cwrap('PDFiumJS_Bitmap_destroy', null, ['number']),
						Page_destroy: cwrap('PDFiumJS_Page_destroy', null, ['number']),
					};
					PDFiumJS.C.init();

					PDFiumJS.opened_files = [];
					var cur_doc = null;
					var cur_file_id = 0;

					var xhr = new XMLHttpRequest();
					xhr.onreadystatechange = function() {
						if (this.readyState == 4 && this.status == 200) {
							console.log(this.response, typeof this.response);

							const reader = new FileReader();
							reader.onload = function(e){
								var buf = new Uint8Array(e.target.result);

								if (cur_doc) {
									PDFiumJS.C.Doc_delete(cur_doc);
									PDFiumJS.opened_files[cur_file_id] = null;
								}
								cur_file_id = PDFiumJS.opened_files.length;
								PDFiumJS.opened_files[cur_file_id] = buf;
								cur_doc = PDFiumJS.C.Doc_new(cur_file_id, buf.length);
								var page_count = PDFiumJS.C.Doc_get_page_count(cur_doc);
								canvas_list.length = 0;

								var cur_page = 0;

								function render_next_page() {
									if (cur_page == page_count) {
										total_page = page_count;
										the_curr_page = 0;

										var canvas2 = document.getElementById('the-canvas');
										var ctx2 = canvas2.getContext('2d');
										ctx2.putImageData(img_list[0], 0, 0);

										document.getElementById("curr_page").value = 1;

										init();

										return;
									}

									try {
										var start_time = Date.now();

										var canvas = document.getElementById('the-canvas');
										var page = PDFiumJS.C.Doc_get_page(cur_doc, cur_page);
										var width = PDFiumJS.C.Page_get_width(page);
										var height = PDFiumJS.C.Page_get_height(page);
										console.log("width : " + width);
										console.log("height : " + height);
										canvas.style.width = width + 'px';
										canvas.style.height = height + 'px';

										width *= Math.round(devicePixelRatio);
										height *= Math.round(devicePixelRatio);

										canvas.width = width;
										canvas.height = height;

										var bitmap = PDFiumJS.C.Page_get_bitmap(page, width, height);
										PDFiumJS.C.Page_destroy(page);

										var buf = PDFiumJS.C.Bitmap_get_buffer(bitmap);
										var stride = PDFiumJS.C.Bitmap_get_stride(bitmap);

										var ctx = canvas.getContext('2d');
										var img = ctx.createImageData(width, height);
										var data = img.data;
										img_list.push(img);

										var off = 0;
										for (var h = 0; h < height; ++h) {
											var ptr = buf + stride * h;
											for (var w = 0; w < width; ++w) {
												data[off++] = HEAPU8[ptr + 2];
												data[off++] = HEAPU8[ptr + 1];
												data[off++] = HEAPU8[ptr];
												data[off++] = 255;
												ptr += 4;
											}
										}

										PDFiumJS.C.Bitmap_destroy(bitmap);

										var end_time = Date.now();
										console.log('Page', cur_page + 1, end_time - start_time, 'ms');
										} catch (e) {
										console.log('Cannot render page', cur_page, e);
									}
									++cur_page;
									timer = setTimeout(render_next_page, 1);
								}
								clearTimeout(timer);
								timer = setTimeout(render_next_page, 1);
							};
							reader.readAsArrayBuffer(this.response);

						}
					}
					xhr.open('GET', pathfile);
					xhr.responseType = 'blob';
					xhr.send();

				},
				_main: function() {

					if (typeof PDFiumJS === 'undefined') {
						(typeof window !== 'undefined' ? window : this).PDFiumJS = {};
					}

					window.pathx = "<?=$val_id;?>";
					Module.getPDF("<?=$val_id;?>");
				}
			};
		</script>

		<script>
			function changePage(choice) {
				if (choice == 'prev') {
					if (the_curr_page > 0)
					{
						the_curr_page--;

						ctx_global.clearRect(0, 0, w, h);
						ctx_global.putImageData(img_list[the_curr_page], 0, 0);
						first_init = true;

						document.getElementById("curr_page").value = the_curr_page + 1;

						llx=0;
						lly=0;
						urx=0;
						ury=0;
					}
					} else if (choice == 'next') {
					if (the_curr_page < total_page - 1)
					{
						the_curr_page++;

						ctx_global.clearRect(0, 0, w, h);
						ctx_global.putImageData(img_list[the_curr_page], 0, 0);
						first_init = true;

						document.getElementById("curr_page").value = the_curr_page + 1;

						llx=0;
						lly=0;
						urx=0;
						ury=0;
					}
				}

				return;
			}
		</script>
		
		<script src="pdfium.js"></script>
		<script src="jquery-3.2.1.min.js"></script>
	</body>

</html>