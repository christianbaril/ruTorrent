if ( plugin.canChangeColumns() ) {
    plugin.config = theWebUI.config;
    theWebUI.config = function( data ) {
        this.tables.trt.columns.push({
            text: 'Owner',
            width: '100px',
            id: 'owner',
            type: TYPE_STRING
        });

        plugin.config.call(this, data);

        plugin.reqOwner = theRequestManager.addRequest('trt', theRequestManager
                .map('d.get_custom=')
                + 'owner', function( hash, torrent, value ) {
            torrent.owner = value;
        });
    };

    plugin.onRemove = function() {
        theWebUI.getTable('trt').removeColumnById('owner');
        theRequestManager.removeRequest('trt', plugin.reqOwner);
    };
}

log('plugin initialized');
