/*
this work is placed by its authors in the public domain.
it was created from scratch, and no part of it was copied from elsewhere.
it can be used, copied, modified, redistributed, as-is or modified,
	whole or in part, without restrictions.
it can be embedded in a copyright protected work, as long as it's clear
	that the copyright does not apply to the embedded parts themselves.
please do not claim for yourself copyrights for this work or parts of it.
the work comes with no warranty or guarantee, stated or implied, including
	fitness for a particular purpose.
*/
"use strict";
$(function() {
	var
		pieceImageUrl = {},
		flipImageUrl,
		boardImageUrl,
		WHITE = 'l',
		BLACK = 'd',
		acode = 'a'.charCodeAt(0),
		moveBucket = [], // this is a scratch thing, but since we access it from different objects, it's convenient to have it global
		anim = 1000,
		sides = ['n', 'e', 's', 'w'], // used for legends
		brainDamage = $.client.profile().name == 'msie', // do not allow resize, do not use svg images.
		defaultBlockSize = 40,
		rtlregex = /[א-ת]/,
		wrapperSelector = 'div.pgn-source-wrapper',
		gameChangeEvent = 'pgn-geme-changed';

// some global, utility functions.
	function bindex(file, row) { return 8 * file + row; }
	function file(ind) { return Math.floor(ind / 8);}
	function row(ind) { return ind % 8; }
	function sign(a, b) { return a == b ? 0 : (a < b ? 1 : -1); }
	function fileOfStr(file) { return file && file.charCodeAt(0) - acode;}
	function rowOfStr(row) { return row && (row - 1);}
	function boardToFen(board) {
		var res = [],
		len = function(s) { return s.length; };
 
		for (var r = 0; r < 8; r++) {
			var row = '';
			for (var f = 0; f < 8; f++)  
				row += board[bindex(f, r)] ? board[bindex(f, r)].fen() : ' ';
			res.push(row.replace(/(\s+)/g, len));
		}
		return res.reverse().join('/'); // fen begins with row 8 file a - go figure...
	}

	function linkMoveClick(e) {
		var
			$this = $(this),
			game = $this.data('game'),
			index = $this.data('index'),
			noAnim = $this.data('noAnim');
		game.gs.clearTimer();
		game.showMoveTo(index, noAnim);
	}

	function Gameset() { // set of functions and features that depend on blocksize, flip and currentGame.
		$.extend(this, {
			blockSize: defaultBlockSize,
			flip: false,
			needRefresh: false,
			allGames: [],
			currentGame: null,
			showDetails:false,
			timer: null,
			top: function(row, l) { return (((this.flip ? row : (7 - row)) + (l ? 0.3 : 0)) * this.blockSize + 20) + 'px'; },
			left: function(file, l) { return (((this.flip ? 7 - file : file) + (l ? 0.5 : 0)) * this.blockSize + 20) + 'px'; },
			legendLocation: function(side, num) {
				var n = 0.5 + num;
				switch (side) {
					case 'n':
						return {top: 0, left: this.left(num, true)};
					case 'e':
						return {top: this.top(num, true), left: this.blockSize * 8 + 20};
					case 's':
						return {top: this.blockSize * 8 + 20, left: this.left(num, true)};
					case 'w':
						return {top: this.top(num, true), left: 10};
				}
			},
			relocateLegends: function() {
				for (var si in sides)
					for (var n = 0; n < 8; n++)
						this[sides[si]][n].css(this.legendLocation(sides[si], n));
			},
			selectGame: function(val) {
				var game = this.allGames[val];
				if (game) {
					game.analyzePgn();
					this.currentGame = game;
					game.show();
					$( '.pgn-comment-toggler' ).trigger( gameChangeEvent );
				}
			},
			drawIfNeedRefresh: function() {
				if (this.needRefresh && this.currentGame)
					this.currentGame.drawBoard();
				this.needRefresh = false;
			},
			changeAppearance: function() {
				this.needRefresh = true;
				this.currentGame.drawBoard();
				this.relocateLegends();
			},
			setWidth: function(width) {
				var
					widthPx = width * 8,
					widthPxPlus = widthPx + 40,
					widthPxPlusPlus = widthPx + 80;
				this.blockSize = width;
				this.boardImg.css({width: widthPx, height: widthPx});
				this.currentGame.tds.boardDiv.css({width: widthPxPlus, height: widthPxPlus});
				this.currentGame.tds.pgnDiv.css({maxHeight: widthPxPlusPlus});
				this.currentGame.tds.descriptionsDiv.css({maxHeight: widthPxPlusPlus});
				this.changeAppearance();
				this.currentGame.showCurrentMoveLink();
			},
			doFlip: function() {
				this.flip ^= 1;
				this.changeAppearance();
				return this.flip;
			},
			clearTimer: function() {
				if (this.timer)
					clearInterval(this.timer);
				this.timer = null;
				this.currentGame.tds.playButton.attr('value', '\u25BA');
			},
			play: function() {
				if (this.timer)
					this.clearTimer();
				else {
					var cg = this.currentGame;
					this.currentGame.wrapAround();
					this.clearTimer();
					cg.advance();
					this.timer = setInterval(function(){cg.advance()}, 1000 + anim);
					this.currentGame.tds.playButton.attr('value', '\u275A\u275A');
				}
			}
		});
	}
 
	function ChessPiece(type, color, game) {
		this.game = game;
		this.type = type;
		this.color = color;
		this.img = $('<img>', {src: pieceImageUrl[type + color], 'class': 'pgn-chessPiece'})
			.toggle(false);
	}
 
	ChessPiece.prototype.appear = function(file, row) {
		this.img.css({top: this.game.gs.top(row), left: this.game.gs.left(file), width: this.game.gs.blockSize})
			.fadeIn(anim);
	};
 
	ChessPiece.prototype.showMove = function(file, row) {
		var gameSet = this.game.gs;
		this.img.animate({top: gameSet.top(row), left: gameSet.left(file)}, anim, function() { gameSet.drawIfNeedRefresh(); });
	};
 
	ChessPiece.prototype.disappear = function() { this.img.fadeOut(anim); }
 
	ChessPiece.prototype.setSquare = function(file, row) {
		this.file = file;
		this.row = row;
		this.onBoard = true;
	};
 
	ChessPiece.prototype.capture = function(file, row) {
		if (this.type == 'p' && !this.game.pieceAt(file, row))  // en passant
			this.game.clearPieceAt(file, this.row);
		else
			this.game.clearPieceAt(file, row);
		this.move(file, row);
	}
 
	ChessPiece.prototype.move = function(file, row) {
		this.game.clearSquare(this.file, this.row);
		this.game.pieceAt(file, row, this); // place it on the board)
		this.game.registerMove({what:'m', piece: this, file: file, row: row})
	}
 
	ChessPiece.prototype.pawnDirection = function() { return this.color == WHITE ? 1 : -1; }
 
	ChessPiece.prototype.toString = function(fen) { return this.type + this.color; }
 
	ChessPiece.prototype.fen = function() { return this.color == WHITE ? this.type.toUpperCase() : this.type; }
 
	ChessPiece.prototype.pawnStart = function() { return this.color == WHITE ? 1 : 6; }
 
	ChessPiece.prototype.remove = function() { this.onBoard = false; }
 
	ChessPiece.prototype.canMoveTo = function(file, row, capture) {
		if (!this.onBoard)
			return false;
		var rd = Math.abs(this.row - row), fd = Math.abs(this.file - file);
		switch(this.type) {
			case 'n':
				return rd * fd == 2; // how nice that 2 is prime: its only factors are 2 and 1....
			case 'p':
				var dir = this.pawnDirection();
				return (
					((this.row == this.pawnStart() && row ==  this.row + dir * 2 && !fd && this.game.roadIsClear(this.file, file, this.row, row) && !capture)
					|| (this.row + dir == row && fd == !!capture))); // advance 1, and either stay in file and no capture, or move exactly one
			case 'k':
				return (rd | fd) == 1; // we'll accept 1 and 1 or 1 and 0.
			case 'q':
				return (rd - fd) * rd * fd == 0 && this.game.roadIsClear(this.file, file, this.row, row); // same row, same file or same diagonal.
			case 'r':
				return rd * fd == 0 && this.game.roadIsClear(this.file, file, this.row, row);
			case 'b':
				return rd == fd && this.game.roadIsClear(this.file, file, this.row, row);
		}
	}
 
	ChessPiece.prototype.matches = function(oldFile, oldRow, isCapture, file, row) {
		if (typeof oldFile == 'number' && oldFile != this.file)
			return false;
		if (typeof oldRow  == 'number' && oldRow != this.row)
			return false;
		return this.canMoveTo(file, row, isCapture);
	}
 
	ChessPiece.prototype.showAction = function(move) {
		switch (move.what) {
			case 'a':
				this.appear(move.file, move.row);
				break;
			case 'm':
				this.showMove(move.file, move.row);
				break;
			case 'r':
				this.disappear();
				break;
		}
	}
 
	function Game(tds, gameSet) {
		$.extend(this, {
			board: [],
			boards: [],
			pieces: [],
			moves: [],
			linkOfIndex: [],
			index: 0,
			piecesByTypeCol: {},
			descriptions: {},
			comments: [],
			analyzed: false,
			tds: tds,
			gs: gameSet});
	}
 
	Game.prototype.show = function() {
		var desc = $.extend({}, this.descriptions),
			rtl = desc['Direction'] == 'rtl',
			num = '',
			tds = this.tds;
 
		// cleanup from previous game.
		this.gs.clearTimer();
		tds.descriptionsDiv.empty();
		tds.pgnDiv.empty();
		tds.boardDiv.find('img.pgn-chessPiece').toggle(false);
 
		// setup descriptions
		delete desc['Direction'];
		tds.descriptionsDiv.css({ direction: rtl ? 'rtl' : 'ltr', textAlign: rtl ? 'right' : 'left' });
		$.each(desc, function(key, val) { tds.descriptionsDiv.append(key + ': ' + val + '<br />'); });
 
		// setup pgn section
		this.linkOfIndex = [];
		for (var i = 0; i < Math.max(this.moves.length, this.comments.length); i++) {
			var move = this.moves[i],
				comment = this.comments[i];
			if (move && move.s) {
				if (move.a)
					num = move.s;
				if (move.turn == 'd')
					num = num.replace(/\.*$/, '...');
				var link = $('<span>', {'class': (move.a ? 'pgn-steplink' : 'pgn-movelink')})
					.text(move.s.replace(/-/g, '\u2011')) // replace hyphens with non-breakable hyphens, to avoid linebreak within O-O or 1-0
					.data({game: this, index: i, noAnim: move.a, moveNum: num})
					.click(linkMoveClick);
				tds.pgnDiv.append(link);
				this.linkOfIndex[i] = link;
			}
			if (comment) {
				var commentSpan = $('<span>', {'class': 'pgn-comment'}).text(this.comments[i]).appendTo(tds.pgnDiv);
				if (rtlregex.test(comment)) {
					commentSpan.addClass('pgn-rtl-comment');
				}
			}
		}
 
		// set the board.
		$(this.pieces).each(function(i, piece){piece.img.appendTo(tds.boardDiv);});
		this.showMoveTo(this.index, true);
	}
 
	Game.prototype.copyBoard = function() { return this.board.slice(); }
 
	Game.prototype.pieceAt = function(file, row, piece) {
		var i = bindex(file, row);
		if (piece) {
			this.board[i] = piece;
			piece.setSquare(file, row);
		}
		return this.board[i];
	}
 
	Game.prototype.clearSquare = function(file, row) {
		delete this.board[bindex(file, row)];
	}
 
	Game.prototype.clearPieceAt = function(file, row) {
		var
			piece = this.pieceAt(file, row);
		if (piece)
			piece.remove();
		this.clearSquare(file, row);
		this.registerMove({what:'r', piece: piece, file: file, row: row})
	}
 
	Game.prototype.roadIsClear = function(file1, file2, row1, row2) {
		var file, row, dfile, drow, moves = 0;
		dfile = sign(file1, file2);
		drow = sign(row1, row2);
		var file = file1, row = row1;
		while (true) {
			file += dfile;
			row += drow;
			if (file == file2 && row == row2)
				return true;
			if (this.pieceAt(file, row))
				return false;
			if (moves++ > 10)
				throw 'something is wrong in function roadIsClear.' +
					' file=' + file + ' file1=' + file1 + ' file2=' + file2 +
					' row=' + row + ' row1=' + row1 + ' row2=' + row2 +
					' dfile=' + dfile + ' drow=' + drow;
		}
	}
 
	Game.prototype.addPieceToDicts = function(piece) {
		this.pieces.push(piece);
		var type = piece.type, color = piece.color;
		var byType = this.piecesByTypeCol[type];
		if (! byType)
			byType = this.piecesByTypeCol[type] = {};
		var byTypeCol = byType[color];
		if (!byTypeCol)
			byTypeCol = byType[color] = [];
		byTypeCol.push(piece);
	}
 
	Game.prototype.registerMove = function(move) {
		function act() { this.piece.showAction(this) };
		moveBucket.push($.extend(move, {act: act}));
	}
 
	Game.prototype.gotoBoard = function(index) {
		this.index = index;
		this.drawBoard();
	}
 
	Game.prototype.advance = function(delta) {
		var m = this.index + (delta || 1); // no param means 1 forward.
		if (0 <= m && m < this.moves.length) {
			this.showMoveTo(m);
			if (this.moves[this.index].a)
				this.advance(delta);
		}
		if (this.index == this.moves.length - 1)
			this.gs.clearTimer();
	}
 
	Game.prototype.showCurrentMoveLink = function() {
		var moveLink = this.linkOfIndex[this.index];
		if (moveLink) {
			moveLink.addClass('pgn-current-move').siblings().removeClass('pgn-current-move');
			var wannabe = moveLink.parent().height() / 2,
				isNow = moveLink.position().top,
				newScrolltop = moveLink.parent()[0].scrollTop + isNow - wannabe;
			moveLink.parent().stop().animate({scrollTop: newScrolltop}, 500);
		}
	}
 
	Game.prototype.showMoveTo = function(index, noAnim) {
		var dif = index - this.index;
		if (noAnim || dif < 1 || 2 < dif)
			this.gotoBoard(index);
		else
			while (this.index < index)
				$.each(this.moves[++this.index].bucket, function(index, drop) {drop.act()});
		this.showCurrentMoveLink();
	}
 
	Game.prototype.drawBoard = function() {
		var
			saveAnim = anim,
			board = this.boards[this.index];
		anim = 0;
		for (var i in this.pieces)
			this.pieces[i].disappear();
		for (var i in board)
			board[i].appear(file(i), row(i));
		anim = saveAnim;
	}
 
	Game.prototype.wrapAround = function() {
		if (this.index >= this.boards.length - 1)
			this.gotoBoard(0);
	}
 
	Game.prototype.kingSideCastle = function(color) {
		var king = this.piecesByTypeCol['k'][color][0];
		var rook = this.pieceAt(7, (color == WHITE ? 0 : 7));
		if (!rook || rook.type != 'r')
			throw 'attempt to castle without rook on appropriate square';
		king.move(fileOfStr('g'), king.row);
		rook.move(fileOfStr('f'), rook.row);
	}
 
	Game.prototype.queenSideCastle = function(color) {
		var king = this.piecesByTypeCol['k'][color][0];
		var rook = this.pieceAt(0, (color == WHITE ? 0 : 7));
		if (!rook || rook.type != 'r')
			throw 'attempt to castle without rook on appropriate square';
		king.move(fileOfStr('c'), king.row);
		rook.move(fileOfStr('d'), rook.row);
	}
 
	Game.prototype.promote = function(piece, type, file, row, capture) {
		piece[capture ? 'capture' : 'move'](file, row);
		this.clearPieceAt(file, row);
		var newPiece = this.createPiece(type, piece.color, file, row);
		this.registerMove({what:'a', piece: newPiece, file: file, row: row});
	}
 
	Game.prototype.createTemplate = function(all) {
		function oneTemplate(board, flip, header, footer) {
			return '{{לוח שחמט מ-FEN|fen=' + boardToFen(board) 
				+ (flip ? '|הפוך=כן\n' : '') 
				+  (header ? '|פתיח=' + header : '')
				+  (footer ? '|כיתוב=' + footer : '')
				+ '}}';
		}
 
		function boardsEqual(b1, b2) {
			if (b1.length != b2.length)
				return false;
			for (var i = 0; i < b1.length; i++)
				if (b1[i] != b2[i])
					return false;
			return true;
		}
 
		function getLinkStr(link) {
			if (! link)
				return '';
			return (link.data('moveNum') || '') + ' ' + link.text();
		}
 
		var content = '',
			header,
			first, 
			last,
			board = [];
 
		if (all) { 
			first = 2;
			last = this.boards.length - 1;
		}  else {
			first = last = this.index;
			header = '<div style="text-align:' + (this.descriptions['Direction'] == 'rtl' ? 'right' : 'left') + '">\n';
			for (var desc in  this.descriptions)
				header += desc + ' ' + this.descriptions[desc] + '<br />\n';
			header += '</div>';
		}
 
		for (var index = first; index <= last; index++) {
			if (! boardsEqual(this.boards[index], board)) {
				var footer = getLinkStr(this.linkOfIndex[index])
					+ (this.comments[index] ? '<br />\n' + this.comments[index] : '');
				board = this.boards[index];
				content += oneTemplate(board, this.gs.flip, header, footer) + '\n';
			}
		}
 
		mw.loader.using('jquery.ui.dialog', function() {
			var ta = $('<textarea>', {value: content, rows: 12}),
				div = $('<div>').dialog().append(ta);
			ta.select();
		});
	}
 
	Game.prototype.createPiece = function(type, color, file, row) {
		var piece = new ChessPiece(type, color, this);
		this.pieceAt(file, row, piece);
		this.addPieceToDicts(piece);
		return piece;
	}
 
	Game.prototype.createMove = function(color, moveStr) {
		moveStr = moveStr.replace(/^\s+|[!?+# ]*(\$\d{1,3})?$/g, ''); // check, mate, comments, glyphs.
		if (!moveStr.length)
			return false;
		if (moveStr == 'O-O')
			return this.kingSideCastle(color);
		if (moveStr == 'O-O-O')
			return this.queenSideCastle(color);
		if ($.inArray(moveStr, ['1-0', '0-1', '1/2-1/2', '*']) + 1)
			return moveStr; // end of game - white wins, black wins, draw, game halted/abandoned/unknown.
		var match = moveStr.match(/([RNBKQ])?([a-h])?([1-8])?(x)?([a-h])([1-8])(=[RNBKQ])?/);
		if (!match) {
			return false;
		}
 
		var type = match[1] ? match[1].toLowerCase() : 'p',
			oldFile = fileOfStr(match[2]),
			oldRow = rowOfStr(match[3]),
			isCapture = !!match[4],
			file = fileOfStr(match[5]),
			row = rowOfStr(match[6]),
			promotion = match[7],
			thePiece = $(this.piecesByTypeCol[type][color]).filter(function() {
					return this.matches(oldFile, oldRow, isCapture, file, row);
				});
		if (thePiece.length != 1) {
			var ok = false;
			if (thePiece.length == 2) { // maybe one of them can't move because it protects the king?
				var king = this.piecesByTypeCol['k'][color][0];
				for (var i = 0; i < 2; i++) {
					var piece = thePiece[i];
					delete this.board[bindex(piece.file, piece.row)];
					for (var j in this.board) {
						var threat = this.board[j];
						if (threat && threat.color != color && threat.canMoveTo(king.file, king.row, true)) {
							ok = true;
							thePiece = thePiece[1-i];
							break;
						}
					}
					this.pieceAt(piece.file, piece.row, piece);
					if (ok)
						break;
				}
 
			}
			if (!ok)
				throw 'could not find matching pieces. type="' + type + ' color=' + color + ' moveAGN="' + moveStr + '". found ' + thePiece.length + ' matching pieces';
		}
		else
			thePiece = thePiece[0];
		if (promotion)
			this.promote(thePiece, promotion.toLowerCase().charAt(1), file, row, isCapture);
		else if (isCapture)
			thePiece.capture(file, row);
		else
			thePiece.move(file, row);
		return moveStr;
	}
 
	Game.prototype.addComment = function(str) {
		this.comments[this.moves.length] = str;
	}
 
	Game.prototype.addDescription = function(description) {
		description = $.trim(description);
		var match = description.match(/\[([^"]+)"(.*)"\]/);
		if (match)
			this.descriptions[$.trim(match[1])] = match[2];
	}
 
	Game.prototype.description = function(pgn) {
		var d = this.descriptions,
			round = d['Round'] ? ' (' + d['Round'] + ')' : '',
			s = d['Name'] 
			|| d['שם'] 
			|| ( (d['Event'] || d['אירוע'] || '') + ': ' + (d['White'] || d['לבן'] || '') + ' - ' + (d['Black'] || d['שחור'] || '') + round);
		return s;
	}
 
	Game.prototype.addMoveLink = function(str, noAnim, turn) {
		this.boards.push(this.board.slice());
		this.moves.push({bucket: moveBucket, s: str, a: noAnim, turn: turn});
		moveBucket = [];
	}

	Game.prototype.preAnalyzePgn = function(pgn) {
		var
			match,
			turn,
			indexOfMove = {},
			moveNum = '';
 
		function removeHead(match) {
			var ind = pgn.indexOf(match) + match.length;
			pgn = pgn.substring(ind);
			return match;
		}

		function tryMatch(regex) {
			var rmatch = pgn.match(regex);
			if (rmatch) {
				removeHead(rmatch[0]);
				moveNum = rmatch[1] || moveNum;;
			}
			return rmatch && rmatch[0];
		}
 
		while (match = tryMatch(/^\s*\[[^\]]*\]/))
			this.addDescription(match);

		this.pgn = pgn; 
	}

	Game.prototype.analyzePgn = function() {
		if (this.analyzed) return;
		this.analyzed = true;
		var
			match,
			turn,
			indexOfMove = {},
			moveNum = '',
			pgn = this.pgn;
 
		function removeHead(match) {
			var ind = pgn.indexOf(match) + match.length;
			pgn = pgn.substring(ind);
			return match;
		}
 
		function tryMatch(regex) {
			var rmatch = pgn.match(regex);
			if (rmatch) {
				removeHead(rmatch[0]);
				moveNum = rmatch[1] || moveNum;;
			}
			return rmatch && rmatch[0];
		}
 
		pgn = pgn.replace(/;(.*)\n/g, ' {$1} ').replace(/\s+/g, ' '); // replace to-end-of-line comments with block comments, remove newlines and noramlize spaces to 1
		this.populateBoard(this.descriptions.FEN || 'rnbqkbnr/pppppppp/8/8/8/8/PPPPPPPP/RNBQKBNR');
		var prevLen = -1;
		this.addMoveLink();
		while (pgn.length) {
			if (prevLen == pgn.length)
				throw "analysePgn encountered a problem. pgn is: " + pgn;
			prevLen = pgn.length;
			if (match = tryMatch(/^\s*\{[^\}]*\}\s*/))
				this.addComment(match);
			if (match = tryMatch(/^\s*\([^\)]*\)\s*/))
				this.addComment(match);
			if (match = tryMatch(/^\s*(\d+)\.+/)) {
				turn = /\.\.\./.test(match) ? BLACK : WHITE;
				this.addMoveLink(match, true);
				continue;
			}
			if (match = tryMatch(/^\s*[^ ]+ ?/)) {
				this.createMove(turn, match);
				this.addMoveLink(match, false, turn);
				indexOfMove[moveNum + turn] = this.moves.length - 1;
				turn = BLACK;
			}
		}
 
		var showFirst = this.descriptions['FirstMove'];
		this.index = (showFirst && indexOfMove[showFirst]) || this.moves.length - 1;
	}
 
	Game.prototype.populateBoard = function(fen) {
		var fenar = fen.split(/[\/\s]/);
		if (fenar.length < 8)
		throw 'illegal fen: "' + fen + '"';
		for (var row = 0; row < 8; row++) {
			var file = 0;
			var filear = fenar[row].split('');
			for (var i in filear) {
				var p = filear[i], lp = p.toLowerCase();
				if (/[1-8]/.test(p))
					file += parseInt(p, 10);
				else if (/[prnbkq]/.test(lp))
					this.createPiece(lp, (p == lp ? BLACK : WHITE), file++, 7-row)
				else
					throw 'illegal fen: "' + fen + '"';
			}
		}
	}
 
	function selectGame() {
		var gameSet = $(this).data('gameSet');
		gameSet.selectGame(this.value);
	}
 
	function createFlipper(gameSet) {
		var flipper =
			$('<img>', {src: flipImageUrl})
				.css({width: '25px', height: '25px', border: 'solid 1px gray', borderRadius: '4px', backgroundColor: '#ddd'})
				.click(function() {
					var
						rotation = gameSet.doFlip() ? 'rotate(180deg)' : 'rotate(0deg)';
					$(this).css({
						'-webkit-transform': rotation,
						'-moz-transform': rotation,
						'-ms-transform': rotation,
						'-o-transform': rotation,
						'transform': rotation});
				});
		return flipper;
	}
 
	function advanceButton(gameSet, forward, allTheway) {
		var legend =
			allTheway
				? (forward ? '|\u25B7' : '\u25C1|')
				: (forward ? '<' : '>')
		return $('<input>', {'class': 'pgn-button', type: 'button', rel: 'f', value: legend})
			.click(function() {
				gameSet.clearTimer();
				if (allTheway) {
					var links = gameSet.currentGame.linkOfIndex;
					links[forward ? links.length - 1 : 1].click();
				}
				else
					gameSet.currentGame.advance(forward * 2 - 1);
			});
	}
 
	function autoPlayButton(gameSet) {
		return $('<input>', {type: 'button', value: '\u25BA'})
				.click(function() { gameSet.play(); });
	}
 
	function toggleCommentButton(gameSet) {
		return $( '<input>', { type: 'button', value: '{..}' } )
				.addClass( 'pgn-comment-toggler' )
				.click(function() { $( '.pgn-comment' ).toggle() })
				.on( gameChangeEvent, function(e) { 
					$( this ).toggle( gameSet.currentGame.comments.length > 0 )
				});
	}
 
	function setWidth(width, $this) { $this.data('gameSet').setWidth(width); }
 
	function buildBoardDiv(container, selector, gameSet) {
		var
			pgnDiv = $('<div>', {'class': 'pgn-pgndiv'}).css({maxHeight: defaultBlockSize * 8 + 80}),
			descriptionsDiv = $('<div>', {'class': 'pgn-descriptions'}),
			gameSetDiv,
			controlsDiv,
			scrollDiv,
			cdTable,
			flipper = createFlipper(gameSet),
			gotoend = advanceButton(gameSet, true, true),
			forward = advanceButton(gameSet, true),
			backstep = advanceButton(gameSet, false),
			gotostart = advanceButton(gameSet, false, true),
			autoPlay = autoPlayButton(gameSet),
			commentsToggler = toggleCommentButton(gameSet),
			slider,
			makeTemplate = (
				window.makeChessTemplate
				? $('<input>', {type: 'button', value: 'T'}).click(function() {gameSet.currentGame.createTemplate();})
				: ''
				),
			makeManyTemplates = (
				window.makeChessTemplate
				? $('<input>', {type: 'button', value: 'TT'}).click(function() {gameSet.currentGame.createTemplate(true);})
				: ''
				);
 
		if (!brainDamage)
			slider = $('<div>', {'class': 'pgn-slider'})
				.slider({
					max: 60,
					min: 20,
					orientation: 'horizontal',
					value: gameSet.blockSize,
					stop: function() { gameSet.setWidth(parseInt(slider.slider('value'), 10)); }
				});
 
		gameSetDiv = $('<div>', {'class': 'pgn-gameset-div'})
			.css({width: 40 + 8 * gameSet.blockSize});
		controlsDiv = $('<div>', {'class': 'pgn-controls'})
			.css({clear: 'both', textAlign: 'center'})
			.append(gotostart)
			.append(backstep)
			.append(autoPlay)
			.append(forward)
			.append(gotoend)
			.append(flipper)
			.append(commentsToggler)
			.append(makeTemplate)
			.append(makeManyTemplates)
			.append(slider || '');
		gameSet.boardDiv = $('<div>', {'class': 'pgn-board-div'});
		gameSet.boardImg = $('<img>', {'class': 'pgn-board-img', src: boardImageUrl})
			.css({padding: 20})
			.appendTo(gameSet.boardDiv);
		var fl = 'abcdefgh'.split('');
 
		for (var side in sides) {
			var
				s = sides[side],
				isFile = /n|s/.test(s);
			gameSet[s] = [];
			for (var i = 0; i < 8; i++) {
				var sp = $('<span>', {'class': isFile ? 'pgn-file-legend' : 'pgn-row-legend'})
					.text(isFile ? fl[i] : (i + 1))
					.appendTo(gameSet.boardDiv)
					.css(gameSet.legendLocation(s, i));
				gameSet[s][i] = sp;
			}
		}
		var table = $('<table>').css({direction: 'ltr'}).appendTo(container);
		if (selector)
			table.append(
				$('<tr>').append($('<td>', {colspan: 3}).css('text-align', 'center').append(selector))
			);
		table.append($('<tr>')
				.append($('<td>', {valign: 'top'}).append(descriptionsDiv))
				.append($('<td>').append(gameSet.boardDiv))
				.append($('<td>', {valign: 'top'}).append(pgnDiv))
			)
			.append($('<tr>')
				.append($('<td>'))
				.append($('<td>').css('text-align', 'center').append(controlsDiv))
			);
 
		return {boardDiv: gameSet.boardDiv, pgnDiv: pgnDiv, descriptionsDiv: descriptionsDiv, playButton: autoPlay};
	}
 
	function doIt() {
		$(wrapperSelector).each(function() {
			var
				wrapperDiv = $(this),
				pgnSource = $('div.pgn-sourcegame', wrapperDiv),
				boardDiv,
				selector,
				gameSet = new Gameset();
 
			if (pgnSource.length > 1)
				selector = $('<select>').data({gameSet: gameSet}).change(selectGame);
 
			var tds = buildBoardDiv(wrapperDiv, selector, gameSet);
			var ind = 0;
			pgnSource.each(function() {
				try {
					var
						game = new Game(tds, gameSet);
						game.preAnalyzePgn($(this).text());
						wrapperDiv.data({currentGame: game});
					ind++;
					gameSet.allGames.push(game);
					if (selector)
						selector.append($('<option>', {value: gameSet.allGames.length - 1, text: game.description()})
							.css('direction', game.descriptions['Direction'] || 'ltr')
						);
				} catch (e) {
					mw.log('exception in game ' + ind + ' problem is: "' + e + '"');
					if (game && game.descriptions)
						for (var d in game.descriptions)
							mw.log(d + ':' + game.descriptions[d]);
				}
			});
			gameSet.selectGame(0);
			gameSet.setWidth(defaultBlockSize);
		})
	}
 
	function pupulateImages() {
		var
			colors = [WHITE, BLACK],
			allPieces = [],
			types = ['p', 'r', 'n', 'b', 'q', 'k'];
		for (var c in colors)
			for (var t in types)
				allPieces.push('File:Chess ' + types[t] + colors[c] + 't45.svg');
		allPieces.push('File:Yin and Yang.svg');
		if (!brainDamage)
			allPieces.push('File:Chessboard480.png');
		var params = {titles: allPieces.join('|'), prop: 'imageinfo', iiprop: 'url'};
		if (brainDamage)
			params.iiurlwidth = defaultBlockSize;
		var api = new mw.Api();
		api.get(params)
			.done(function(data) {
				if (data && data.query) {
					$.each(data.query.pages, function(index, page) {
						var
							url = page.imageinfo[0][brainDamage ? 'thumburl' : 'url'],
							match = url.match(/Chess_([prnbqk][dl])t45\.svg/); // piece
						if (match)
							pieceImageUrl[match[1]] = url;
						else if (/Yin/.test(url))
							flipImageUrl = url;
						else if (/Chessboard/.test(url))
							boardImageUrl = url;
					});
					if (brainDamage) {
						delete params.iiurlwidth;
						params.titles = 'File:Chessboard480.png';
						api.get(params)
						.done(
							function(data) {
								if (data && data.query) {
									$.each(data.query.pages, function(index, page) {boardImageUrl = page.imageinfo[0].url});
									doIt();
								}
							}
						);
					}
					else
						doIt();
				}
			});
	}
 
	if ($(wrapperSelector).length)
		mw.loader.using(['mediawiki.api', 'jquery.ui.slider'], pupulateImages);
});