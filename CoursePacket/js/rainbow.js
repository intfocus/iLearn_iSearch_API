/* Rainbow v1.1.8 rainbowco.de | included languages: c, shell, java, coffeescript, generic, scheme, javascript, r, haskell, python, html, smalltalk, csharp, go, php, ruby, lua, css */
var m = !0;
window.Rainbow = function() {
	function r(a) {
		var b, c = a.getAttribute && a.getAttribute("data-language") || 0;
		if (!c) {
			a = a.attributes;
			for (b = 0; b < a.length; ++b)
				if ("data-language" === a[b].nodeName) return a[b].nodeValue
		}
		return c
	}

	function C(a) {
		var b = r(a) || r(a.parentNode);
		if (!b) {
			var c = /\blang(?:uage)?-(\w+)/;
			(a = a.className.match(c) || a.parentNode.className.match(c)) && (b = a[1])
		}
		return b
	}

	function D(a, b) {
		for (var c in e[d]) {
			c = parseInt(c, 10);
			if (a == c && b == e[d][c] ? 0 : a <= c && b >= e[d][c]) delete e[d][c], delete j[d][c];
			if (a >= c && a < e[d][c] ||
				b > c && b < e[d][c]) return m
		}
		return !1
	}

	function s(a, b) {
		return '<span class="' + a.replace(/\./g, " ") + (l ? " " + l : "") + '">' + b + "</span>"
	}

	function t(a, b, c, h) {
		var f = a.exec(c);
		if (f) {
			++u;
			!b.name && "string" == typeof b.matches[0] && (b.name = b.matches[0], delete b.matches[0]);
			var k = f[0],
				i = f.index,
				v = f[0].length + i,
				g = function() {
					function f() {
						t(a, b, c, h)
					}
					u % 100 > 0 ? f() : setTimeout(f, 0)
				};
			if (D(i, v)) g();
			else {
				var n = w(b.matches),
					l = function(a, c, h) {
						if (a >= c.length) h(k);
						else {
							var d = f[c[a]];
							if (d) {
								var e = b.matches[c[a]],
									i = e.language,
									g = e.name && e.matches ?
									e.matches : e,
									j = function(b, d, e) {
										var i;
										i = 0;
										var g;
										for (g = 1; g < c[a]; ++g) f[g] && (i = i + f[g].length);
										d = e ? s(e, d) : d;
										k = k.substr(0, i) + k.substr(i).replace(b, d);
										l(++a, c, h)
									};
								i ? o(d, i, function(a) {
									j(d, a)
								}) : typeof e === "string" ? j(d, d, e) : x(d, g.length ? g : [g], function(a) {
									j(d, a, e.matches ? e.name : 0)
								})
							} else l(++a, c, h)
						}
					};
				l(0, n, function(a) {
					b.name && (a = s(b.name, a));
					if (!j[d]) {
						j[d] = {};
						e[d] = {}
					}
					j[d][i] = {
						replace: f[0],
						"with": a
					};
					e[d][i] = v;
					g()
				})
			}
		} else h()
	}

	function w(a) {
		var b = [],
			c;
		for (c in a) a.hasOwnProperty(c) && b.push(c);
		return b.sort(function(a,
			b) {
			return b - a
		})
	}

	function x(a, b, c) {
		function h(b, k) {
			k < b.length ? t(b[k].pattern, b[k], a, function() {
				h(b, ++k)
			}) : E(a, function(a) {
				delete j[d];
				delete e[d];
				--d;
				c(a)
			})
		}++d;
		h(b, 0)
	}

	function E(a, b) {
		function c(a, b, h, e) {
			if (h < b.length) {
				++y;
				var g = b[h],
					l = j[d][g],
					a = a.substr(0, g) + a.substr(g).replace(l.replace, l["with"]),
					g = function() {
						c(a, b, ++h, e)
					};
				0 < y % 250 ? g() : setTimeout(g, 0)
			} else e(a)
		}
		var h = w(j[d]);
		c(a, h, 0, b)
	}

	function o(a, b, c) {
		var d = n[b] || [],
			f = n[z] || [],
			b = A[b] ? d : d.concat(f);
		x(a.replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/&(?![\w\#]+;)/g,
			"&amp;"), b, c)
	}

	function p(a, b, c) {
		if (b < a.length) {
			var d = a[b],
				f = C(d);
			return !(-1 < (" " + d.className + " ").indexOf(" rainbow ")) && f ? (f = f.toLowerCase(), d.className += d.className ? " rainbow" : "rainbow", o(d.innerHTML, f, function(k) {
				d.innerHTML = k;
				j = {};
				e = {};
				q && q(d, f);
				setTimeout(function() {
					p(a, ++b, c)
				}, 0)
			})) : p(a, ++b, c)
		}
		c && c()
	}

	function B(a, b) {
		var a = a && "function" == typeof a.getElementsByTagName ? a : document,
			c = a.getElementsByTagName("pre"),
			d = a.getElementsByTagName("code"),
			f, e = [];
		for (f = 0; f < d.length; ++f) e.push(d[f]);
		for (f = 0; f <
			c.length; ++f) c[f].getElementsByTagName("code").length || e.push(c[f]);
		p(e, 0, b)
	}
	var j = {},
		e = {},
		n = {},
		A = {},
		d = 0,
		z = 0,
		u = 0,
		y = 0,
		l, q;
	return {
		extend: function(a, b, c) {
			1 == arguments.length && (b = a, a = z);
			A[a] = c;
			n[a] = b.concat(n[a] || [])
		},
		c: function(a) {
			q = a
		},
		a: function(a) {
			l = a
		},
		color: function(a, b, c) {
			if ("string" == typeof a) return o(a, b, c);
			if ("function" == typeof a) return B(0, a);
			B(a, b)
		}
	}
}();
window.addEventListener ? window.addEventListener("load", Rainbow.color, !1) : window.attachEvent("onload", Rainbow.color);
Rainbow.onHighlight = Rainbow.c;
Rainbow.addClass = Rainbow.a;
Rainbow.extend("c", [{
	name: "meta.preprocessor",
	matches: {
		1: [{
			matches: {
				1: "keyword.define",
				2: "entity.name"
			},
			pattern: /(\w+)\s(\w+)\b/g
		}, {
			name: "keyword.define",
			pattern: /endif/g
		}, {
			name: "constant.numeric",
			pattern: /\d+/g
		}, {
			matches: {
				1: "keyword.include",
				2: "string"
			},
			pattern: /(include)\s(.*?)$/g
		}]
	},
	pattern: /\#([\S\s]*?)$/gm
}, {
	name: "keyword",
	pattern: /\b(do|goto|continue|break|switch|case|typedef)\b/g
}, {
	name: "entity.label",
	pattern: /\w+:/g
}, {
	matches: {
		1: "storage.type",
		3: "storage.type",
		4: "entity.name.function"
	},
	pattern: /\b((un)?signed|const)? ?(void|char|short|int|long|float|double)\*? +((\w+)(?= ?\())?/g
}, {
	matches: {
		2: "entity.name.function"
	},
	pattern: /(\w|\*) +((\w+)(?= ?\())/g
}, {
	name: "storage.modifier",
	pattern: /\b(static|extern|auto|register|volatile|inline)\b/g
}, {
	name: "support.type",
	pattern: /\b(struct|union|enum)\b/g
}]);
Rainbow.extend("shell", [{
		name: "shell",
		matches: {
			1: {
				language: "shell"
			}
		},
		pattern: /\$\(([\s\S]*?)\)/gm
	}, {
		matches: {
			2: "string"
		},
		pattern: /(\(|\s|\[|\=)(('|")[\s\S]*?(\3))/gm
	}, {
		name: "keyword.operator",
		pattern: /&lt;|&gt;|&amp;/g
	}, {
		name: "comment",
		pattern: /\#[\s\S]*?$/gm
	}, {
		name: "storage.function",
		pattern: /(.+?)(?=\(\)\s{0,}\{)/g
	}, {
		name: "support.command",
		pattern: /\b(echo|rm|ls|(mk|rm)dir|cd|find|cp|exit|pwd|exec|trap|source|shift|unset)/g
	}, {
		matches: {
			1: "keyword"
		},
		pattern: /\b(break|case|continue|do|done|elif|else|esac|eval|export|fi|for|function|if|in|local|return|set|then|unset|until|while)(?=\(|\b)/g
	}],
	m);
Rainbow.extend("java", [{
		name: "constant",
		pattern: /\b(false|null|true|[A-Z_]+)\b/g
	}, {
		b: {
			1: "keyword",
			2: "support.namespace"
		},
		pattern: /(import|package)\s(.+)/g
	}, {
		name: "keyword",
		pattern: /\b(abstract|assert|boolean|break|byte|case|catch|char|class|const|continue|default|do|double|else|enum|extends|final|finally|float|for|goto|if|implements|import|instanceof|int|interface|long|native|new|package|private|protected|public|return|short|static|strictfp|super|switch|synchronized|this|throw|throws|transient|try|void|volatile|while)\b/g
	}, {
		name: "string",
		pattern: /(".*?")/g
	}, {
		name: "char",
		pattern: /(')(.|\\.|\\u[\dA-Fa-f]{4})\1/g
	}, {
		name: "integer",
		pattern: /\b(0x[\da-f]+|\d+)L?\b/g
	}, {
		name: "comment",
		pattern: /\/\*[\s\S]*?\*\/|(\/\/).*?$/gm
	}, {
		name: "support.annotation",
		pattern: /@\w+/g
	}, {
		b: {
			1: "entity.function"
		},
		pattern: /([^@\.\s]+)\(/g
	}, {
		name: "entity.class",
		pattern: /\b([A-Z]\w*)\b/g
	}, {
		name: "operator",
		pattern: /(\+{1,2}|-{1,2}|~|!|\*|\/|%|(?:&lt;){1,2}|(?:&gt;){1,3}|instanceof|(?:&amp;){1,2}|\^|\|{1,2}|\?|:|(?:=|!|\+|-|\*|\/|%|\^|\||(?:&lt;){1,2}|(?:&gt;){1,3})?=)/g
	}],
	m);
Rainbow.extend("coffeescript", [{
	name: "comment.block",
	pattern: /(\#{3})[\s\S]*\1/gm
}, {
	name: "string.block",
	pattern: /('{3}|"{3})[\s\S]*\1/gm
}, {
	name: "string.regex",
	matches: {
		2: {
			name: "comment",
			pattern: /\#(.*?)\n/g
		}
	},
	pattern: /(\/{3})([\s\S]*)\1/gm
}, {
	matches: {
		1: "keyword"
	},
	pattern: /\b(in|when|is|isnt|of|not|unless|until|super)(?=\b)/gi
}, {
	name: "keyword.operator",
	pattern: /\?/g
}, {
	name: "constant.language",
	pattern: /\b(undefined|yes|on|no|off)\b/g
}, {
	name: "keyword.variable.coffee",
	pattern: /@(\w+)/gi
}, {
	name: "reset",
	pattern: /object|class|print/gi
}, {
	matches: {
		1: "entity.name.function",
		2: "keyword.operator",
		3: {
			name: "function.argument.coffee",
			pattern: /([\@\w]+)/g
		},
		4: "keyword.function"
	},
	pattern: /(\w+)\s{0,}(=|:)\s{0,}\((.*?)((-|=)&gt;)/gi
}, {
	matches: {
		1: {
			name: "function.argument.coffee",
			pattern: /([\@\w]+)/g
		},
		2: "keyword.function"
	},
	pattern: /\s\((.*?)\)\s{0,}((-|=)&gt;)/gi
}, {
	matches: {
		1: "entity.name.function",
		2: "keyword.operator",
		3: "keyword.function"
	},
	pattern: /(\w+)\s{0,}(=|:)\s{0,}((-|=)&gt;)/gi
}, {
	matches: {
		1: "storage.class",
		2: "entity.name.class",
		3: "storage.modifier.extends",
		4: "entity.other.inherited-class"
	},
	pattern: /\b(class)\s(\w+)(\sextends\s)?([\w\\]*)?\b/g
}, {
	matches: {
		1: "keyword.new",
		2: {
			name: "support.class",
			pattern: /\w+/g
		}
	},
	pattern: /\b(new)\s(.*?)(?=\s)/g
}]);
Rainbow.extend([{
	matches: {
		1: {
			name: "keyword.operator",
			pattern: /\=/g
		},
		2: {
			name: "string",
			matches: {
				name: "constant.character.escape",
				pattern: /\\('|"){1}/g
			}
		}
	},
	pattern: /(\(|\s|\[|\=|:)(('|")([^\\\1]|\\.)*?(\3))/gm
}, {
	name: "comment",
	pattern: /\/\*[\s\S]*?\*\/|(\/\/|\#)[\s\S]*?$/gm
}, {
	name: "constant.numeric",
	pattern: /\b(\d+(\.\d+)?(e(\+|\-)?\d+)?(f|d)?|0x[\da-f]+)\b/gi
}, {
	matches: {
		1: "keyword"
	},
	pattern: /\b(and|array|as|bool(ean)?|c(atch|har|lass|onst)|d(ef|elete|o(uble)?)|e(cho|lse(if)?|xit|xtends|xcept)|f(inally|loat|or(each)?|unction)|global|if|import|int(eger)?|long|new|object|or|pr(int|ivate|otected)|public|return|self|st(ring|ruct|atic)|switch|th(en|is|row)|try|(un)?signed|var|void|while)(?=\(|\b)/gi
}, {
	name: "constant.language",
	pattern: /true|false|null/g
}, {
	name: "keyword.operator",
	pattern: /\+|\!|\-|&(gt|lt|amp);|\||\*|\=/g
}, {
	matches: {
		1: "function.call"
	},
	pattern: /(\w+?)(?=\()/g
}, {
	matches: {
		1: "storage.function",
		2: "entity.name.function"
	},
	pattern: /(function)\s(.*?)(?=\()/g
}]);
Rainbow.extend("scheme", [{
		name: "plain",
		pattern: /&gt;|&lt;/g
	}, {
		name: "comment",
		pattern: /;.*$/gm
	}, {
		name: "constant.language",
		pattern: /#t|#f|'\(\)/g
	}, {
		name: "constant.symbol",
		pattern: /'[^()\s#]+/g
	}, {
		name: "constant.number",
		pattern: /\b\d+(?:\.\d*)?\b/g
	}, {
		name: "string",
		pattern: /".+?"/g
	}, {
		matches: {
			1: "storage.function",
			2: "variable"
		},
		pattern: /\(\s*(define)\s+\(?(\S+)/g
	}, {
		matches: {
			1: "keyword"
		},
		pattern: /\(\s*(begin|define\-syntax|if|lambda|quasiquote|quote|set!|syntax\-rules|and|and\-let\*|case|cond|delay|do|else|or|let|let\*|let\-syntax|letrec|letrec\-syntax)(?=[\]()\s#])/g
	}, {
		matches: {
			1: "entity.function"
		},
		pattern: /\(\s*(eqv\?|eq\?|equal\?|number\?|complex\?|real\?|rational\?|integer\?|exact\?|inexact\?|=|<|>|<=|>=|zero\?|positive\?|negative\?|odd\?|even\?|max|min|\+|\-|\*|\/|abs|quotient|remainder|modulo|gcd|lcm|numerator|denominator|floor|ceiling|truncate|round|rationalize|exp|log|sin|cos|tan|asin|acos|atan|sqrt|expt|make\-rectangular|make\-polar|real\-part|imag\-part|magnitude|angle|exact\->inexact|inexact\->exact|number\->string|string\->number|not|boolean\?|pair\?|cons|car|cdr|set\-car!|set\-cdr!|caar|cadr|cdar|cddr|caaar|caadr|cadar|caddr|cdaar|cdadr|cddar|cdddr|caaaar|caaadr|caadar|caaddr|cadaar|cadadr|caddar|cadddr|cdaaar|cdaadr|cdadar|cdaddr|cddaar|cddadr|cdddar|cddddr|null\?|list\?|list|length|append|reverse|list\-tail|list\-ref|memq|memv|member|assq|assv|assoc|symbol\?|symbol\->string|string\->symbol|char\?|char=\?|char<\?|char>\?|char<=\?|char>=\?|char\-ci=\?|char\-ci<\?|char\-ci>\?|char\-ci<=\?|char\-ci>=\?|char\-alphabetic\?|char\-numeric\?|char\-whitespace\?|char\-upper\-case\?|char\-lower\-case\?|char\->integer|integer\->char|char\-upcase|char\-downcase|string\?|make\-string|string|string\-length|string\-ref|string\-set!|string=\?|string\-ci=\?|string<\?|string>\?|string<=\?|string>=\?|string\-ci<\?|string\-ci>\?|string\-ci<=\?|string\-ci>=\?|substring|string\-append|string\->list|list\->string|string\-copy|string\-fill!|vector\?|make\-vector|vector|vector\-length|vector\-ref|vector\-set!|vector\->list|list\->vector|vector\-fill!|procedure\?|apply|map|for\-each|force|call\-with\-current\-continuation|call\/cc|values|call\-with\-values|dynamic\-wind|eval|scheme\-report\-environment|null\-environment|interaction\-environment|call\-with\-input\-file|call\-with\-output\-file|input\-port\?|output\-port\?|current\-input\-port|current\-output\-port|with\-input\-from\-file|with\-output\-to\-file|open\-input\-file|open\-output\-file|close\-input\-port|close\-output\-port|read|read\-char|peek\-char|eof\-object\?|char\-ready\?|write|display|newline|write\-char|load|transcript\-on|transcript\-off)(?=[\]()\s#])/g
	}],
	m);
Rainbow.extend("javascript", [{
	name: "selector",
	pattern: /(\s|^)\$(?=\.|\()/g
}, {
	name: "support",
	pattern: /\b(window|document)\b/g
}, {
	matches: {
		1: "support.property"
	},
	pattern: /\.(length|node(Name|Value))\b/g
}, {
	matches: {
		1: "support.function"
	},
	pattern: /(setTimeout|setInterval)(?=\()/g
}, {
	matches: {
		1: "support.method"
	},
	pattern: /\.(getAttribute|push|getElementById|getElementsByClassName|log|setTimeout|setInterval)(?=\()/g
}, {
	matches: {
		1: "support.tag.script",
		2: [{
			name: "string",
			pattern: /('|")(.*?)(\1)/g
		}, {
			name: "entity.tag.script",
			pattern: /(\w+)/g
		}],
		3: "support.tag.script"
	},
	pattern: /(&lt;\/?)(script.*?)(&gt;)/g
}, {
	name: "string.regexp",
	matches: {
		1: "string.regexp.open",
		2: {
			name: "constant.regexp.escape",
			pattern: /\\(.){1}/g
		},
		3: "string.regexp.close",
		4: "string.regexp.modifier"
	},
	pattern: /(\/)(?!\*)(.+)(\/)([igm]{0,3})/g
}, {
	matches: {
		1: "storage",
		3: "entity.function"
	},
	pattern: /(var)?(\s|^)(.*)(?=\s?=\s?function\()/g
}, {
	matches: {
		1: "keyword",
		2: "entity.function"
	},
	pattern: /(new)\s+(.*)(?=\()/g
}, {
	name: "entity.function",
	pattern: /(\w+)(?=:\s{0,}function)/g
}]);
Rainbow.extend("r", [{
	matches: {
		1: {
			name: "keyword.operator",
			pattern: /\=|<\-|&lt;-/g
		},
		2: {
			name: "string",
			matches: {
				name: "constant.character.escape",
				pattern: /\\('|"){1}/g
			}
		}
	},
	pattern: /(\(|\s|\[|\=|:)(('|")([^\\\1]|\\.)*?(\3))/gm
}, {
	matches: {
		1: "constant.language"
	},
	pattern: /\b(NULL|NA|TRUE|FALSE|T|F|NaN|Inf|NA_integer_|NA_real_|NA_complex_|NA_character_)\b/g
}, {
	matches: {
		1: "constant.symbol"
	},
	pattern: /[^0-9a-zA-Z\._](LETTERS|letters|month\.(abb|name)|pi)/g
}, {
	name: "keyword.operator",
	pattern: /&lt;-|<-|-|==|&lt;=|<=|&gt;>|>=|<|>|&amp;&amp;|&&|&amp;|&|!=|\|\|?|\*|\+|\^|\/|%%|%\/%|\=|%in%|%\*%|%o%|%x%|\$|:|~|\[{1,2}|\]{1,2}/g
}, {
	matches: {
		1: "storage",
		3: "entity.function"
	},
	pattern: /(\s|^)(.*)(?=\s?=\s?function\s\()/g
}, {
	matches: {
		1: "storage.function"
	},
	pattern: /[^a-zA-Z0-9._](function)(?=\s*\()/g
}, {
	matches: {
		1: "namespace",
		2: "keyword.operator",
		3: "function.call"
	},
	pattern: /([a-zA-Z][a-zA-Z0-9._]+)([:]{2,3})([.a-zA-Z][a-zA-Z0-9._]*(?=\s*\())\b/g
}, {
	name: "support.function",
	pattern: /(^|[^0-9a-zA-Z\._])(array|character|complex|data\.frame|double|integer|list|logical|matrix|numeric|vector)(?=\s*\()/g
}]);
Rainbow.extend("haskell", [{
	name: "comment",
	pattern: /\{\-\-[\s\S(\w+)]+[\-\-][\}$]/gm
}, {
	name: "comment",
	pattern: /\-\-(.*)/g
}, {
	matches: {
		1: "keyword",
		2: "support.namespace"
	},
	pattern: /\b(module)\s(\w+)\s[\(]?(\w+)?[\)?]\swhere/g
}, {
	name: "keyword.operator",
	pattern: /\+|\!|\-|&(gt|lt|amp);|\/\=|\||\@|\:|\.|\+{2}|\:|\*|\=|#|\.{2}|(\\)[a-zA-Z_]/g
}, {
	name: "keyword",
	pattern: /\b(case|class|foreign|hiding|qualified|data|family|default|deriving|do|else|if|import|in|infix|infixl|infixr|instance|let|in|otherwise|module|newtype|of|then|type|where)\b/g
}, {
	name: "keyword",
	pattern: /[\`][a-zA-Z_']*?[\`]/g
}, {
	matches: {
		1: "keyword",
		2: "keyword.operator"
	},
	pattern: /\b(infix|infixr|infixl)+\s\d+\s(\w+)*/g
}, {
	name: "entity.class",
	pattern: /\b([A-Z][A-Za-z0-9_']*)/g
}, {
	name: "meta.preprocessor",
	matches: {
		1: [{
			matches: {
				1: "keyword.define",
				2: "entity.name"
			},
			pattern: /(\w+)\s(\w+)\b/g
		}, {
			name: "keyword.define",
			pattern: /endif/g
		}, {
			name: "constant.numeric",
			pattern: /\d+/g
		}, {
			matches: {
				1: "keyword.include",
				2: "string"
			},
			pattern: /(include)\s(.*?)$/g
		}]
	},
	pattern: /^\#([\S\s]*?)$/gm
}]);
Rainbow.extend("python", [{
	name: "variable.self",
	pattern: /self/g
}, {
	name: "constant.language",
	pattern: /None|True|False/g
}, {
	name: "support.object",
	pattern: /object/g
}, {
	name: "support.function.python",
	pattern: /\b(bs|divmod|input|open|staticmethod|all|enumerate|int|ord|str|any|eval|isinstance|pow|sum|basestring|execfile|issubclass|print|super|bin|file|iter|property|tuple|bool|filter|len|range|type|bytearray|float|list|raw_input|unichr|callable|format|locals|reduce|unicode|chr|frozenset|long|reload|vars|classmethod|getattr|map|repr|xrange|cmp|globals|max|reversed|zip|compile|hasattr|memoryview|round|__import__|complex|hash|min|set|apply|delattr|help|next|setattr|buffer|dict|hex|object|slice|coerce|dir|id|oct|sorted|intern)(?=\()/g
}, {
	matches: {
		1: "keyword"
	},
	pattern: /\b(pass|lambda|with|is|not|in|from|elif)(?=\(|\b)/g
}, {
	matches: {
		1: "storage.class",
		2: "entity.name.class",
		3: "entity.other.inherited-class"
	},
	pattern: /(class)\s+(\w+)\((\w+?)\)/g
}, {
	matches: {
		1: "storage.function",
		2: "support.magic"
	},
	pattern: /(def)\s+(__\w+)(?=\()/g
}, {
	name: "support.magic",
	pattern: /__(name)__/g
}, {
	matches: {
		1: "keyword.control",
		2: "support.exception.type"
	},
	pattern: /(except) (\w+):/g
}, {
	matches: {
		1: "storage.function",
		2: "entity.name.function"
	},
	pattern: /(def)\s+(\w+)(?=\()/g
}, {
	name: "entity.name.function.decorator",
	pattern: /@(\w+)/g
}, {
	name: "comment.docstring",
	pattern: /('{3}|"{3})[\s\S]*?\1/gm
}]);
Rainbow.extend("html", [{
	name: "source.php.embedded",
	matches: {
		2: {
			language: "php"
		}
	},
	pattern: /&lt;\?=?(?!xml)(php)?([\s\S]*?)(\?&gt;)/gm
}, {
	name: "source.css.embedded",
	matches: {
		"0": {
			language: "css"
		}
	},
	pattern: /&lt;style(.*?)&gt;([\s\S]*?)&lt;\/style&gt;/gm
}, {
	name: "source.js.embedded",
	matches: {
		"0": {
			language: "javascript"
		}
	},
	pattern: /&lt;script(?! src)(.*?)&gt;([\s\S]*?)&lt;\/script&gt;/gm
}, {
	name: "comment.html",
	pattern: /&lt;\!--[\S\s]*?--&gt;/g
}, {
	matches: {
		1: "support.tag.open",
		2: "support.tag.close"
	},
	pattern: /(&lt;)|(\/?\??&gt;)/g
}, {
	name: "support.tag",
	matches: {
		1: "support.tag",
		2: "support.tag.special",
		3: "support.tag-name"
	},
	pattern: /(&lt;\??)(\/|\!?)(\w+)/g
}, {
	matches: {
		1: "support.attribute"
	},
	pattern: /([a-z-]+)(?=\=)/gi
}, {
	matches: {
		1: "support.operator",
		2: "string.quote",
		3: "string.value",
		4: "string.quote"
	},
	pattern: /(=)('|")(.*?)(\2)/g
}, {
	matches: {
		1: "support.operator",
		2: "support.value"
	},
	pattern: /(=)([a-zA-Z\-0-9]*)\b/g
}, {
	matches: {
		1: "support.attribute"
	},
	pattern: /\s(\w+)(?=\s|&gt;)(?![\s\S]*&lt;)/g
}], m);
Rainbow.extend("smalltalk", [{
	name: "keyword.pseudovariable",
	pattern: /self|thisContext/g
}, {
	name: "keyword.constant",
	pattern: /false|nil|true/g
}, {
	name: "string",
	pattern: /'([^']|'')*'/g
}, {
	name: "string.symbol",
	pattern: /#\w+|#'([^']|'')*'/g
}, {
	name: "string.character",
	pattern: /\$\w+/g
}, {
	name: "comment",
	pattern: /"([^"]|"")*"/g
}, {
	name: "constant.numeric",
	pattern: /-?\d+(\.\d+)?((r-?|s)[A-Za-z0-9]+|e-?[0-9]+)?/g
}, {
	name: "entity.name.class",
	pattern: /\b[A-Z]\w*/g
}, {
	name: "entity.name.function",
	pattern: /\b[a-z]\w*:?/g
}, {
	name: "entity.name.binary",
	pattern: /(&lt;|&gt;|&amp;|[=~\|\\\/!@*\-_+])+/g
}, {
	name: "operator.delimiter",
	pattern: /;[\(\)\[\]\{\}]|#\[|#\(^\./g
}], m);
Rainbow.extend("csharp", [{
		name: "constant",
		pattern: /\b(false|null|true)\b/g
	}, {
		name: "keyword",
		pattern: /\b(abstract|add|alias|ascending|as|base|bool|break|byte|case|catch|char|checked|class|const|continue|decimal|default|delegate|descending|double|do|dynamic|else|enum|event|explicit|extern|false|finally|fixed|float|foreach|for|from|get|global|goto|group|if|implicit|int|interface|internal|into|in|is|join|let|lock|long|namespace|new|object|operator|orderby|out|override|params|partial|private|protected|public|readonly|ref|remove|return|sbyte|sealed|select|set|short|sizeof|stackalloc|static|string|struct|switch|this|throw|try|typeof|uint|unchecked|ulong|unsafe|ushort|using|value|var|virtual|void|volatile|where|while|yield)\b/g
	}, {
		matches: {
			1: "keyword",
			2: {
				name: "support.class",
				pattern: /\w+/g
			}
		},
		pattern: /(typeof)\s([^\$].*?)(\)|;)/g
	}, {
		matches: {
			1: "keyword.namespace",
			2: {
				name: "support.namespace",
				pattern: /\w+/g
			}
		},
		pattern: /\b(namespace)\s(.*?);/g
	}, {
		matches: {
			1: "storage.modifier",
			2: "storage.class",
			3: "entity.name.class",
			4: "storage.modifier.extends",
			5: "entity.other.inherited-class"
		},
		pattern: /\b(abstract|sealed)?\s?(class)\s(\w+)(\sextends\s)?([\w\\]*)?\s?\{?(\n|\})/g
	}, {
		name: "keyword.static",
		pattern: /\b(static)\b/g
	}, {
		matches: {
			1: "keyword.new",
			2: {
				name: "support.class",
				pattern: /\w+/g
			}
		},
		pattern: /\b(new)\s([^\$].*?)(?=\)|\(|;|&)/g
	}, {
		name: "string",
		pattern: /(")(.*?)\1/g
	}, {
		name: "integer",
		pattern: /\b(0x[\da-f]+|\d+)\b/g
	}, {
		name: "comment",
		pattern: /\/\*[\s\S]*?\*\/|(\/\/)[\s\S]*?$/gm
	}, {
		name: "operator",
		pattern: /(\+\+|\+=|\+|--|-=|-|&lt;&lt;=|&lt;&lt;|&lt;=|=&gt;|&gt;&gt;=|&gt;&gt;|&gt;=|!=|!|~|\^|\|\||&amp;&amp;|&amp;=|&amp;|\?\?|::|:|\*=|\*|\/=|%=|\|=|==|=)/g
	}, {
		name: "preprocessor",
		pattern: /(\#if|\#else|\#elif|\#endif|\#define|\#undef|\#warning|\#error|\#line|\#region|\#endregion|\#pragma)[\s\S]*?$/gm
	}],
	m);
Rainbow.extend("go", [{
	matches: {
		1: {
			name: "keyword.operator",
			pattern: /\=/g
		},
		2: {
			name: "string",
			matches: {
				name: "constant.character.escape",
				pattern: /\\(`|"){1}/g
			}
		}
	},
	pattern: /(\(|\s|\[|\=|:)((`|")([^\\\1]|\\.)*?(\3))/gm
}, {
	name: "comment",
	pattern: /\/\*[\s\S]*?\*\/|(\/\/)[\s\S]*?$/gm
}, {
	name: "constant.numeric",
	pattern: /\b(\d+(\.\d+)?(e(\+|\-)?\d+)?(f|d)?|0x[\da-f]+)\b/gi
}, {
	matches: {
		1: "keyword"
	},
	pattern: /\b(break|c(ase|onst|ontinue)|d(efault|efer)|else|fallthrough|for|go(to)?|if|import|interface|map|package|range|return|select|struct|switch|type|var)(?=\(|\b)/gi
}, {
	name: "constant.language",
	pattern: /true|false|null|string|byte|rune|u?int(8|16|32|64)?|float(32|64)|complex(64|128)/g
}, {
	name: "keyword.operator",
	pattern: /\+|\!|\-|&(gt|lt|amp);|\||\*|\:?=/g
}, {
	matches: {
		1: "function.call"
	},
	pattern: /(\w+?)(?=\()/g
}, {
	matches: {
		1: "storage.function",
		2: "entity.name.function"
	},
	pattern: /(func)\s(.*?)(?=\()/g
}]);
Rainbow.extend("php", [{
	name: "support",
	pattern: /\becho\b/g
}, {
	matches: {
		1: "variable.dollar-sign",
		2: "variable"
	},
	pattern: /(\$)(\w+)\b/g
}, {
	name: "constant.language",
	pattern: /true|false|null/ig
}, {
	name: "constant",
	pattern: /\b[A-Z0-9_]{2,}\b/g
}, {
	name: "keyword.dot",
	pattern: /\./g
}, {
	name: "keyword",
	pattern: /\b(continue|break|die|end(for(each)?|switch|if)|case|require(_once)?|include(_once)?)(?=\(|\b)/g
}, {
	matches: {
		1: "keyword",
		2: {
			name: "support.class",
			pattern: /\w+/g
		}
	},
	pattern: /(instanceof)\s([^\$].*?)(\)|;)/g
}, {
	matches: {
		1: "support.function"
	},
	pattern: /\b(array(_key_exists|_merge|_keys|_shift)?|isset|count|empty|unset|printf|is_(array|string|numeric|object)|sprintf|each|date|time|substr|pos|str(len|pos|tolower|_replace|totime)?|ord|trim|in_array|implode|end|preg_match|explode|fmod|define|link|list|get_class|serialize|file|sort|mail|dir|idate|log|intval|header|chr|function_exists|dirname|preg_replace|file_exists)(?=\()/g
}, {
	name: "variable.language.php-tag",
	pattern: /(&lt;\?(php)?|\?&gt;)/g
}, {
	matches: {
		1: "keyword.namespace",
		2: {
			name: "support.namespace",
			pattern: /\w+/g
		}
	},
	pattern: /\b(namespace|use)\s(.*?);/g
}, {
	matches: {
		1: "storage.modifier",
		2: "storage.class",
		3: "entity.name.class",
		4: "storage.modifier.extends",
		5: "entity.other.inherited-class",
		6: "storage.modifier.extends",
		7: "entity.other.inherited-class"
	},
	pattern: /\b(abstract|final)?\s?(class|interface|trait)\s(\w+)(\sextends\s)?([\w\\]*)?(\simplements\s)?([\w\\]*)?\s?\{?(\n|\})/g
}, {
	name: "keyword.static",
	pattern: /self::|static::/g
}, {
	matches: {
		1: "storage.function",
		2: "support.magic"
	},
	pattern: /(function)\s(__.*?)(?=\()/g
}, {
	matches: {
		1: "keyword.new",
		2: {
			name: "support.class",
			pattern: /\w+/g
		}
	},
	pattern: /\b(new)\s([^\$].*?)(?=\)|\(|;)/g
}, {
	matches: {
		1: {
			name: "support.class",
			pattern: /\w+/g
		},
		2: "keyword.static"
	},
	pattern: /([\w\\]*?)(::)(?=\b|\$)/g
}, {
	matches: {
		2: {
			name: "support.class",
			pattern: /\w+/g
		}
	},
	pattern: /(\(|,\s?)([\w\\]*?)(?=\s\$)/g
}]);
Rainbow.extend("ruby", [{
	name: "string",
	matches: {
		1: "string.open",
		2: {
			name: "string.keyword",
			pattern: /(\#\{.*?\})/g
		},
		3: "string.close"
	},
	pattern: /("|`)(.*?[^\\\1])?(\1)/g
}, {
	name: "string",
	pattern: /('|"|`)([^\\\1\n]|\\.)*\1/g
}, {
	name: "string",
	pattern: /%[qQ](?=(\(|\[|\{|&lt;|.)(.*?)(?:'|\)|\]|\}|&gt;|\1))(?:\(\2\)|\[\2\]|\{\2\}|\&lt;\2&gt;|\1\2\1)/g
}, {
	matches: {
		1: "string",
		2: "string",
		3: "string"
	},
	pattern: /(&lt;&lt;)(\w+).*?$([\s\S]*?^\2)/gm
}, {
	matches: {
		1: "string",
		2: "string",
		3: "string"
	},
	pattern: /(&lt;&lt;\-)(\w+).*?$([\s\S]*?\2)/gm
}, {
	name: "string.regexp",
	matches: {
		1: "string.regexp",
		2: {
			name: "string.regexp",
			pattern: /\\(.){1}/g
		},
		3: "string.regexp",
		4: "string.regexp"
	},
	pattern: /(\/)(.*?)(\/)([a-z]*)/g
}, {
	name: "string.regexp",
	matches: {
		1: "string.regexp",
		2: {
			name: "string.regexp",
			pattern: /\\(.){1}/g
		},
		3: "string.regexp",
		4: "string.regexp"
	},
	pattern: /%r(?=(\(|\[|\{|&lt;|.)(.*?)('|\)|\]|\}|&gt;|\1))(?:\(\2\)|\[\2\]|\{\2\}|\&lt;\2&gt;|\1\2\1)([a-z]*)/g
}, {
	name: "comment",
	pattern: /#.*$/gm
}, {
	name: "comment",
	pattern: /^\=begin[\s\S]*?\=end$/gm
}, {
	matches: {
		1: "constant"
	},
	pattern: /(\w+:)[^:]/g
}, {
	matches: {
		1: "constant.symbol"
	},
	pattern: /[^:](:(?:\w+|(?=['"](.*?)['"])(?:"\2"|'\2')))/g
}, {
	name: "constant.numeric",
	pattern: /\b(0x[\da-f]+|\d+)\b/g
}, {
	name: "support.class",
	pattern: /\b[A-Z]\w*(?=((\.|::)[A-Za-z]|\[))/g
}, {
	name: "constant",
	pattern: /\b[A-Z]\w*\b/g
}, {
	matches: {
		1: "storage.class",
		2: "entity.name.class",
		3: "entity.other.inherited-class"
	},
	pattern: /\s*(class)\s+((?:(?:::)?[A-Z]\w*)+)(?:\s+&lt;\s+((?:(?:::)?[A-Z]\w*)+))?/g
}, {
	matches: {
		1: "storage.module",
		2: "entity.name.class"
	},
	pattern: /\s*(module)\s+((?:(?:::)?[A-Z]\w*)+)/g
}, {
	name: "variable.global",
	pattern: /\$([a-zA-Z_]\w*)\b/g
}, {
	name: "variable.class",
	pattern: /@@([a-zA-Z_]\w*)\b/g
}, {
	name: "variable.instance",
	pattern: /@([a-zA-Z_]\w*)\b/g
}, {
	matches: {
		1: "keyword.control"
	},
	pattern: /[^\.]\b(BEGIN|begin|case|class|do|else|elsif|END|end|ensure|for|if|in|module|rescue|then|unless|until|when|while)\b(?![?!])/g
}, {
	matches: {
		1: "keyword.control.pseudo-method"
	},
	pattern: /[^\.]\b(alias|alias_method|break|next|redo|retry|return|super|undef|yield)\b(?![?!])|\bdefined\?|\bblock_given\?/g
}, {
	matches: {
		1: "constant.language"
	},
	pattern: /\b(nil|true|false)\b(?![?!])/g
}, {
	matches: {
		1: "variable.language"
	},
	pattern: /\b(__(FILE|LINE)__|self)\b(?![?!])/g
}, {
	matches: {
		1: "keyword.special-method"
	},
	pattern: /\b(require|gem|initialize|new|loop|include|extend|raise|attr_reader|attr_writer|attr_accessor|attr|catch|throw|private|module_function|public|protected)\b(?![?!])/g
}, {
	name: "keyword.operator",
	pattern: /\s\?\s|=|&lt;&lt;|&lt;&lt;=|%=|&=|\*=|\*\*=|\+=|\-=|\^=|\|{1,2}=|&lt;&lt;|&lt;=&gt;|&lt;(?!&lt;|=)|&gt;(?!&lt;|=|&gt;)|&lt;=|&gt;=|===|==|=~|!=|!~|%|&amp;|\*\*|\*|\+|\-|\/|\||~|&gt;&gt;/g
}, {
	matches: {
		1: "keyword.operator.logical"
	},
	pattern: /[^\.]\b(and|not|or)\b/g
}, {
	matches: {
		1: "storage.function",
		2: "entity.name.function"
	},
	pattern: /(def)\s(.*?)(?=(\s|\())/g
}], m);
Rainbow.extend("lua", [{
	matches: {
		1: {
			name: "keyword.operator",
			pattern: /\=/g
		},
		2: {
			name: "string",
			matches: {
				name: "constant.character.escape",
				pattern: /\\('|"){1}/g
			}
		}
	},
	pattern: /(\(|\s|\[|\=)(('|")([^\\\1]|\\.)*?(\3))/gm
}, {
	name: "comment",
	pattern: /\-{2}\[{2}\-{2}[\s\S]*?\-{2}\]{2}\-{2}|(\-{2})[\s\S]*?$/gm
}, {
	name: "constant.numeric",
	pattern: /\b(\d+(\.\d+)?(e(\+|\-)?\d+)?(f|d)?|0x[\da-f]+)\b/gi
}, {
	matches: {
		1: "keyword"
	},
	pattern: /\b((a|e)nd|in|repeat|break|local|return|do|for|then|else(if)?|function|not|if|or|until|while)(?=\(|\b)/gi
}, {
	name: "constant.language",
	pattern: /true|false|nil/g
}, {
	name: "keyword.operator",
	pattern: /\+|\!|\-|&(gt|lt|amp);|\||\*|\=|#|\.{2}/g
}, {
	matches: {
		1: "storage.function",
		2: "entity.name.function"
	},
	pattern: /(function)\s+(\w+[\:|\.]?\w+?)(?=\()/g
}, {
	matches: {
		1: "support.function"
	},
	pattern: /\b(print|require|module|\w+\.\w+)(?=\()/g
}]);
Rainbow.extend("css", [{
		name: "comment",
		pattern: /\/\*[\s\S]*?\*\//gm
	}, {
		name: "constant.hex-color",
		pattern: /#([a-f0-9]{3}|[a-f0-9]{6})(?=;|\s)/gi
	}, {
		matches: {
			1: "constant.numeric",
			2: "keyword.unit"
		},
		pattern: /(\d+)(px|em|cm|s|%)?/g
	}, {
		name: "string",
		pattern: /('|")(.*?)\1/g
	}, {
		name: "support.css-property",
		matches: {
			1: "support.vendor-prefix"
		},
		pattern: /(-o-|-moz-|-webkit-|-ms-)?[\w-]+(?=\s?:)(?!.*\{)/g
	}, {
		matches: {
			1: [{
				name: "entity.name.sass",
				pattern: /&amp;/g
			}, {
				name: "direct-descendant",
				pattern: /&gt;/g
			}, {
				name: "entity.name.class",
				pattern: /\.[\w\-_]+/g
			}, {
				name: "entity.name.id",
				pattern: /\#[\w\-_]+/g
			}, {
				name: "entity.name.pseudo",
				pattern: /:[\w\-_]+/g
			}, {
				name: "entity.name.tag",
				pattern: /\w+/g
			}]
		},
		pattern: /([\w\ ,:\.\#\&\;\-_]+)(?=.*\{)/g
	}, {
		matches: {
			2: "support.vendor-prefix",
			3: "support.css-value"
		},
		pattern: /(:|,)\s?(-o-|-moz-|-webkit-|-ms-)?([a-zA-Z-]*)(?=\b)(?!.*\{)/g
	}, {
		matches: {
			1: "support.tag.style",
			2: [{
				name: "string",
				pattern: /('|")(.*?)(\1)/g
			}, {
				name: "entity.tag.style",
				pattern: /(\w+)/g
			}],
			3: "support.tag.style"
		},
		pattern: /(&lt;\/?)(style.*?)(&gt;)/g
	}],
	m);