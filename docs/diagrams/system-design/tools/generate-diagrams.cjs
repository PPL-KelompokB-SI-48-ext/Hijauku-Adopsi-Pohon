const fs = require('node:fs');
const path = require('node:path');
const root = process.argv[2] || path.resolve(__dirname, '../../../../../');
const base = path.join(root, 'docs/diagrams/system-design');
const sql = fs.readFileSync(path.join(base, 'erd-schema.mysql.sql'), 'utf8');
const tables = {};
for (const m of sql.matchAll(/CREATE TABLE\s+(\w+)\s*\((.*?)\)\s*ENGINE=InnoDB;/gis)) {
  const name = m[1], body = m[2], columns = [], primary = new Set(), uniques = [], foreignKeys = [];
  for (const raw of body.split(/\r?\n/)) {
    const line = raw.trim().replace(/,$/, ''), c = line.match(/^(\w+)\s+(.+)$/);
    if (c && !/^(PRIMARY KEY|UNIQUE KEY|KEY |CONSTRAINT )/i.test(line)) columns.push({name:c[1],definition:c[2]});
  }
  for (const x of body.matchAll(/PRIMARY KEY\s*\(([^)]+)\)/gi)) x[1].split(',').forEach(v=>primary.add(v.trim()));
  for (const x of body.matchAll(/UNIQUE KEY\s+\w+\s*\(([^)]+)\)/gi)) uniques.push(x[1].split(',').map(v=>v.trim()));
  for (const x of body.matchAll(/CONSTRAINT\s+\w+\s+FOREIGN KEY\s*\(([^)]+)\)\s+REFERENCES\s+(\w+)\s*\(([^)]+)\)/gi)) {
    foreignKeys.push({local:x[1].split(',').map(v=>v.trim()),target:x[2],remote:x[3].split(',').map(v=>v.trim())});
  }
  tables[name]={columns,primary,uniques,foreignKeys};
}
const relGroups=[
['01-program-sites-plots',['users','programs','sites','plots']],
['02-tree-inventory',['tree_species','plots','planting_batches','trees']],
['03-activities-registration',['users','sites','activities','activity_registrations']],
['04-field-assignments',['users','activities','trees','field_assignments']],
['05-adoption',['users','trees','adoption_requests','adoption_agreements','tree_adoption_allocations']],
['06-care-workflow',['users','trees','field_assignments','care_plans','care_records']],
['07-tree-issues',['users','trees','tree_issues']],
['08-growth-tree',['trees','growth_observations']],
['09-growth-user',['users','growth_observations']],
['10-article-author',['users','environment_articles']],
['11-gallery-upload',['users','activities','gallery_entries']],
['12-proposal-review',['users','green_action_proposals']],
['13-activity-logs',['users','activity_logs']]
];

const id=(t,c)=>'field_'+t+'_'+c;
const common=["  theme: base","  look: classic","  themeVariables:","    background: '#FFFFFF'","    primaryColor: '#F0FDF4'","    primaryTextColor: '#1F2937'","    primaryBorderColor: '#15803D'","    lineColor: '#64748B'","    fontFamily: Arial"];
for(const [group,names] of relGroups){
  const set=new Set(names), rels=[], shown=Object.fromEntries(names.map(n=>[n,new Set(['id'])]));
  for(const child of names) for(const fk of tables[child].foreignKeys) if(set.has(fk.target)){
    rels.push([child,fk]); fk.local.forEach(v=>shown[child].add(v)); fk.remote.forEach(v=>shown[fk.target].add(v));
  }
  const lines=['---','config:',...common,'  flowchart:','    curve: basis','    nodeSpacing: 45','    rankSpacing: 55','    diagramPadding: 28','---','flowchart LR','%% Garis menghubungkan kolom FK ke kolom PK atau UK yang dirujuk.'];
  for(const t of names){
    lines.push('  subgraph tbl_'+t+'["'+t+'"]','    direction TB');
    const cols=[...shown[t]].sort((a,b)=>(a==='id'?-1:b==='id'?1:a.localeCompare(b)));
    for(const c of cols){
      if(!tables[t].columns.some(x=>x.name===c)) continue;
      const flags=[]; if(tables[t].primary.has(c))flags.push('PK'); if(tables[t].foreignKeys.some(f=>f.local.includes(c)))flags.push('FK'); if(!tables[t].primary.has(c)&&tables[t].uniques.some(u=>u.includes(c)))flags.push('UK'); const flag=flags.join('/');
      lines.push('    '+id(t,c)+'["'+c+(flag?' · '+flag:'')+'"]:::'+(flag.includes('FK')?'fk':'pk'));
    }
    lines.push('  end');
  }
  for(const [child,fk] of rels){
    const uq=tables[child].uniques.some(u=>u.length===fk.local.length&&u.every(v=>fk.local.includes(v)));
    const max=uq?'0..1':'0..*';
    fk.local.forEach((local,i)=>lines.push('  '+id(fk.target,fk.remote[i])+' -->|"1 : '+max+(fk.local.length>1?' komposit':'')+'"| '+id(child,local)));
  }
  lines.push('  classDef pk fill:#DCFCE7,stroke:#15803D,stroke-width:2px,color:#14532D','  classDef fk fill:#EFF6FF,stroke:#2563EB,stroke-width:1.5px,color:#1E3A8A','  linkStyle default stroke:#64748B,stroke-width:1.8px');
  fs.writeFileSync(path.join(base,'erd-relations-'+group+'.mmd'),lines.join('\n')+'\n');
}


function xml(value) {
  return String(value).replaceAll('&','&amp;').replaceAll('<','&lt;').replaceAll('>','&gt;').replaceAll('"','&quot;');
}
const exportsDir=path.join(base,'exports'); fs.mkdirSync(exportsDir,{recursive:true});
const connectedSchemaGroups=[
['01-program-sites-plots',['users','programs','sites','plots']],
['02-tree-inventory',['tree_species','plots','planting_batches','trees']],
['03-activities-registration',['users','sites','activities','activity_registrations']],
['04-field-assignments',['users','activities','trees','field_assignments']],
['05a-adoption-requests',['users','trees','adoption_requests']],
['05b-adoption-agreements',['users','trees','adoption_requests','adoption_agreements']],
['05c-adoption-allocations',['trees','adoption_requests','adoption_agreements','tree_adoption_allocations']],
['06-care-plans',['trees','field_assignments','care_plans']],
['07-care-records',['users','trees','care_plans','care_records']],
['08-tree-issues',['users','trees','tree_issues']],
['09-growth-observations',['users','trees','growth_observations']],
['10-article-author',['users','environment_articles']],
['11-gallery-upload',['users','activities','gallery_entries']],
['12-proposal-review',['users','green_action_proposals']],
['13-activity-logs',['users','activity_logs']]
];
function connectedSchemaSvg(group,names) {
  const W=900, cardW=390, gap=76, x0=22, x1=x0+cardW+gap, top=86, rowH=30, headH=44, rowGap=48, bottom=40;
  const cardHeight=t=>headH+tables[t].columns.length*rowH+12;
  const rowHeights=[];
  for(let i=0;i<Math.ceil(names.length/2);i++) rowHeights.push(Math.max(...names.slice(i*2,i*2+2).map(cardHeight)));
  const rowYs=[]; let y=top;
  rowHeights.forEach(h=>{rowYs.push(y);y+=h+rowGap});
  const H=y-rowGap+bottom, positions=new Map(), cards=[];
  for(let i=0;i<names.length;i++){
    const name=names[i], x=i%2===0?x0:x1, cy=rowYs[Math.floor(i/2)], t=tables[name];
    positions.set(name,{x,y:cy,height:cardHeight(name)});
    let s='<g><rect x="'+x+'" y="'+cy+'" width="'+cardW+'" height="'+cardHeight(name)+'" rx="12" fill="#FFFFFF" stroke="'+(['environment_articles','gallery_entries','green_action_proposals'].includes(name)?'#D97706':'#15803D')+'" stroke-width="2"/>';
    const accent=['environment_articles','gallery_entries','green_action_proposals'].includes(name);
    s+='<rect x="'+(x+1)+'" y="'+(cy+1)+'" width="'+(cardW-2)+'" height="'+headH+'" rx="10" fill="'+(accent?'#FFF7ED':'#F0FDF4')+'"/>';
    s+='<text x="'+(x+cardW/2)+'" y="'+(cy+29)+'" text-anchor="middle" font-family="Arial,sans-serif" font-size="15" font-weight="700" fill="#1F2937">'+xml(name.toUpperCase())+'</text>';
    t.columns.forEach((col,j)=>{
      const ry=cy+headH+j*rowH;
      if(j%2===1)s+='<rect x="'+(x+1)+'" y="'+ry+'" width="'+(cardW-2)+'" height="'+rowH+'" fill="#F8FAFC"/>';
      const flags=[]; if(t.primary.has(col.name))flags.push('PK'); if(t.foreignKeys.some(f=>f.local.includes(col.name)))flags.push('FK'); if(!t.primary.has(col.name)&&t.uniques.some(u=>u.includes(col.name)))flags.push('UK'); const flag=flags.join('/');
      const type=(col.definition.match(/^([A-Z]+(?:\([^)]+\))?(?:\s+UNSIGNED)?)/i)||[])[1]||col.definition.split(/\s+/)[0];
      s+='<text x="'+(x+11)+'" y="'+(ry+19)+'" font-family="Arial,sans-serif" font-size="12.5" fill="#1F2937">'+xml(col.name)+'</text>';
      s+='<text x="'+(x+214)+'" y="'+(ry+19)+'" font-family="Arial,sans-serif" font-size="11.5" fill="#64748B">'+xml(type.toUpperCase())+'</text>';
      if(flag)s+='<text x="'+(x+cardW-10)+'" y="'+(ry+18)+'" text-anchor="end" font-family="Arial,sans-serif" font-size="10" font-weight="700" fill="'+(flag.includes('FK')?'#2563EB':(accent?'#D97706':'#15803D'))+'">'+flag+'</text>';
      s+='<line x1="'+(x+1)+'" y1="'+(ry+rowH)+'" x2="'+(x+cardW-1)+'" y2="'+(ry+rowH)+'" stroke="#E2E8F0"/>';
    });
    cards.push(s+'</g>');
  }
  const paths=[]; let edge=0;
  for(const child of names) for(const fk of tables[child].foreignKeys){
    if(!positions.has(fk.target))continue;
    for(let k=0;k<fk.local.length;k++){
      const parent=positions.get(fk.target), target=positions.get(child);
      const py=parent.y+headH+tables[fk.target].columns.findIndex(c=>c.name===fk.remote[k])*rowH+rowH/2;
      const ty=target.y+headH+tables[child].columns.findIndex(c=>c.name===fk.local[k])*rowH+rowH/2;
      let d='';
      if(parent.x<target.x){const sx=parent.x+cardW, tx=target.x;d='M'+sx+' '+py+' C'+(sx+28)+' '+py+' '+(tx-28)+' '+ty+' '+tx+' '+ty;}
      else if(parent.x>target.x){const sx=parent.x, tx=target.x+cardW;d='M'+sx+' '+py+' C'+(sx-28)+' '+py+' '+(tx+28)+' '+ty+' '+tx+' '+ty;}
      else if(parent.x===x0){const sx=parent.x+cardW, tx=target.x+cardW, rail=sx+24+(edge%4)*10;d='M'+sx+' '+py+' H'+rail+' V'+ty+' H'+tx;} else {const sx=parent.x, tx=target.x, rail=sx-24-(edge%4)*10;d='M'+sx+' '+py+' H'+rail+' V'+ty+' H'+tx;}
      paths.push('<path d="'+d+'" fill="none" stroke="#2563EB" stroke-width="1.8" marker-end="url(#fkArrow)"/>');
      edge++;
    }
  }
  let out='<svg xmlns="http://www.w3.org/2000/svg" width="'+W+'" height="'+H+'" viewBox="0 0 '+W+' '+H+'"><defs><marker id="fkArrow" markerWidth="8" markerHeight="8" refX="7" refY="4" orient="auto"><path d="M0 0 L8 4 L0 8 z" fill="#2563EB"/></marker></defs><rect width="100%" height="100%" fill="#F8FAFC"/>';
  out+='<text x="22" y="27" font-family="Arial,sans-serif" font-size="18" font-weight="700" fill="#15803D">SKEMA TABEL · '+xml(group.replace(/^\d+-/, '').replaceAll('-', ' ').toUpperCase())+'</text>';
  out+='<text x="22" y="51" font-family="Arial,sans-serif" font-size="11" fill="#64748B">Garis biru berujung pada kolom FK dan PK/UK yang tepat · DDL tanpa baris data</text>';
  out+=cards.join('')+paths.join('');
  out+='<text x="22" y="'+(H-14)+'" font-family="Arial,sans-serif" font-size="10" fill="#64748B">Panah menunjukkan rujukan FK ke PK/UK. Tipe, kolom, dan kunci mengikuti erd-schema.mysql.sql.</text></svg>';
  return out;
}
for(const [group,names] of connectedSchemaGroups) fs.writeFileSync(path.join(exportsDir,'schema-rel-'+group+'.svg'),connectedSchemaSvg(group,names));
console.log('Tables: '+Object.keys(tables).length+'; exact-field ERDs: '+relGroups.length+'; full-schema SVG groups: '+connectedSchemaGroups.length);
