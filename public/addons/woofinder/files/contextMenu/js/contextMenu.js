!function(t, n, e, i) {
    var o = function(t, n) {
            this.init(t, n)
        };
    o.prototype = {
        init: function(t, n) {
            this.ele = t, this.defaults = {
                menu: [{
                    text: "菜单一",
                    callback: function() {}
                }, {
                    text: "菜单二",
                    callback: function() {}
                }],
                target: function(t) {},
                width: 100,
                itemHeight: 28,
                bgColor: "#fff",
                color: "#333",
                fontSize: 14,
                hoverBgColor: "#f5f5f5"
            }, this.opts = e.extend(!0, {}, this.defaults, n), this.random = (new Date).getTime() + parseInt(1e3 * Math.random()), this.eventBind()
        },
        renderMenu: function(ele) {
            var t = this;
                n = "#uiContextMenu_" + this.random;
            e('ul[id^="uiContextMenu_"]').remove();
            var t = this,
                i = '<ul class="ul-context-menu" id="uiContextMenu_' + this.random + '">';
            e.each(this.opts.menu, function(t, n) {
                i += '<li class="ui-context-menu-item"><a href="javascript:void(0);"><span class="icon"><i class="fa ' + n.icon + '"></i></span><span class="text">' + n.text + "</span></a></li>"
            }), i += "</ul>", e("body").append(i).find(".ul-context-menu").hide(), this.initStyle(n), e(n).on("click", ".ui-context-menu-item", function(n) {
                t.menuItemClick(e(this),ele), n.stopPropagation()
            })
            
        },
        initStyle: function(t) {
            var n = this.opts;
            e(t).css({
                width: n.width,
                backgroundColor: n.bgColor
            }).find(".ui-context-menu-item a").css({
                color: n.color,
                fontSize: n.fontSize,
                height: n.itemHeight,
                lineHeight: n.itemHeight + "px"
            }).hover(function() {
                e(this).css({
                    backgroundColor: n.hoverBgColor
                })
            }, function() {
                e(this).css({
                    backgroundColor: n.bgColor
                })
            })
        },
        menuItemClick: function(t, ele) {
            var n = this,
                e = t.index();
            t.parent(".ul-context-menu").hide(), n.opts.menu[e].callback && "function" == typeof n.opts.menu[e].callback && n.opts.menu[e].callback(ele)
        },
        setPosition: function(t) {
            e("#uiContextMenu_" + this.random).css({
                left: t.clientX + 2,
                top: t.clientY + 2
            }).show()
        },
        eventBind: function() {
            var t = this;
            this.ele.on("contextmenu", function(n) {
                n.preventDefault(), t.renderMenu(this), t.setPosition(n), t.opts.target && "function" == typeof t.opts.target && t.opts.target(e(this))
            }), e(n).on("click", function() {
                e(".ul-context-menu").hide()
            })
        }
    }, e.fn.contextMenu = function(t) {
        return new o(this, t), this
    }
}(window, document, jQuery);