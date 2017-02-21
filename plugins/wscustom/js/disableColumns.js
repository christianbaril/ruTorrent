/**
 * Created with JetBrains PhpStorm.
 * User: barilc
 * Date: 9/17/13
 * Time: 8:32 AM
 */
var cols = theWebUI.getTable('trt').colsdata;

for (i in cols) {
    if (!(disabledColumns.indexOf(cols[i].id) == -1) && cols[i].enabled) {
        theWebUI.getTable('trt').toggleColumn(i);
    }
}

var tableToggleColumn = dxSTable.prototype.toggleColumn;
dxSTable.prototype.toggleColumn = function (i) {
    if (this.colsdata[i].enabled && (disabledColumns.indexOf(cols[i].id) == -1)) {
        // Column not in disabledColumns ?
        tableToggleColumn.call(this, i);
    }
}

dxSTable.prototype.onRightClick = function (e) {
    if (e.which === 3 && !this.isMoving) {
        theContextMenu.clear();
        for (var i = 0; i < this.colsdata.length; i++) {
            var data = this.colsdata[i];
            if (this.colOrder[i] && (disabledColumns.indexOf(cols[i].id) == -1)) {
                var a = [data.text, "theWebUI.getTable('" + this.prefix + "').toggleColumn(" + i + ")"];
                if (data.enabled) {
                    a.unshift(CMENU_SEL);
                }
                theContextMenu.add(a);

            }
        }
        theContextMenu.setNoHide();
        theContextMenu.show(e.clientX, e.clientY);
        return(false);
    }
}
