
var cols = theWebUI.getTable('trt').colsdata;
for (var i = 0; i < cols.length; i++) {
    if (cols[i].id === 'owner') {
        if (cols[i].enabled) {
            theWebUI.getTable('trt').toggleColumn(i);
        }
        break;
    }
}
var tableToggleColumn = dxSTable.prototype.toggleColumn;
dxSTable.prototype.toggleColumn = function (i) {
    if (this.colsdata[i].enabled || this.colsdata[i].id !== 'owner') {
        tableToggleColumn.call(this, i);
    }
}
dxSTable.prototype.onRightClick = function (e) {
    if (e.which === 3 && !this.isMoving) {
        theContextMenu.clear();
        for (var i = 0; i < this.colsdata.length; i++) {
            var data = this.colsdata[i];
            if (this.colOrder[i] && data.id !== 'owner') {
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