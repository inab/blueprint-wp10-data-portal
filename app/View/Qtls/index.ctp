<div class="sixteen wide column">
    <div class="hidden section divider"></div>
    <h1 class="ui header">Search</h1>
    <div class="ui secondary form segment">
        <div class="five fields">
            <div class="inline field">
                <select class="ui search dropdown">
                    <option value="1">Chromosome 1</option>
                    <option value="2">Chromosome 2</option>
                </select>
             </div>
            <div class="field"><input type="text" name="first-name" placeholder="Position (3000:4000)"></div>
            <div class="field"><input type="text" name="first-name" placeholder="Gene"></div>
            <div class="field"><input type="text" name="first-name" placeholder="SNP"></div>
            <div class="field"><input type="text" name="last-name" placeholder="Meth probe"></div>
        </div>
        <div class="two fields">
            <div class="inline field">
                <label>FDR cutoff</label>
                <select class="ui search dropdown">
                    <option value="AF">0.01</option>
                    <option value="AX">0.05</option>
                </select>
                &nbsp;
                <div class="ui slider checkbox">
                  <input type="checkbox">
                  <label>RNA based QTLs</label>
                </div>
                <label></label>
                <div class="ui slider checkbox">
                  <input type="checkbox">
                  <label>Meth QTLs</label>
                </div>
                <label></label>
            </div>
            <div class="inline field">

                &nbsp;<div class="ui primary button">Search</div>
            </div>
        </div>
    </div>
</div>
<div class="sixteen wide column">
    <div class="hidden section divider"></div>
    <table class="ui table">
        <thead>
            <th>SNP</th>
            <th>Meth probe</th>
            <th>Gene</th>
            <th>Monocyte</th>
            <th>Neutrophil</th>
            <th>T-cell</th>
        </thead>
        <tbody>
            <?php foreach ($res['hits']['hits'] as $h):?>
            <tr>
                <td><?php echo $h['_source']['SNP'];?></td>
                <td><?php echo $h['_source']['meth.probe'];?></td>
                <td><?php echo $h['_source']['gid.1'];?></td>
                <td><?php echo $h['_source']['mon.fdr'];?></td>
                <td><?php echo $h['_source']['neu.fdr'];?></td>
                <td><?php echo $h['_source']['tcl.fdr'];?></td>
            </tr>
            <?php endforeach;?>
        </tbody>
    </table>
</div>
