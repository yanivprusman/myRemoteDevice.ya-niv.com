<script src="{{ asset('flatbuffers/flatbuffers.min.js') }}"></script>
<script src="{{ asset('flatbuffers/mrd.min.js') }}"></script>
<div id="content"></div>
<script>
function doIt() {
    let builder = new flatbuffers.Builder(1024);
    const NS = mrd.theMRDNameSpace;
    let dataOffset = builder.createString("Hello, MRD!");
    NS.MRD.startMRD(builder);
    NS.MRD.addType(builder, NS.types.client);
    NS.MRD.addAction(builder, NS.actions.getPage); 
    NS.MRD.addData(builder, dataOffset); 
    let mrdOffset = NS.MRD.endMRD(builder);
    builder.finish(mrdOffset);
    let buf = builder.asUint8Array();
    var content = document.getElementById("content");
    content.innerHTML += "Serialized MRD:" +buf + "<br>";
    let buf2 = new flatbuffers.ByteBuffer(buf); 
    let amrd = NS.MRD.getRootAsMRD(buf2);
    content.innerHTML += "MRD Type: " + NS.types[amrd.type()] + "<br>"; 
    content.innerHTML += "MRD Action: " + NS.actions[amrd.action()] + "<br>"; 
    content.innerHTML += "MRD Data: " + amrd.data() + "<br>"; 
}
doIt();
</script>