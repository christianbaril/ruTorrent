$(document).ready(function () {

    var rTorrentStubStart = rTorrentStub.prototype.start;

    rTorrentStub.prototype.start = function () {
        rTorrentStubStart.call(this);

        this.contentType = "xml"; // fixes bad response from server.

        for (var i = 0; i < this.hashes.length; i++) {

            var owner = theWebUI.getTable('trt').getValueById(this.hashes[i], 'owner');

            if (!owner) {
                var cmd = new rXMLRPCCommand('d.set_custom');
                cmd.addParameter('string', this.hashes[i]);
                cmd.addParameter('string', 'owner');
                cmd.addParameter('string', userinfo.username);
                this.commands.push(cmd);
            }

        }
    };
});