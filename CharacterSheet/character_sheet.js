var _state = { checkboxes: {}, radios: {} };
var _sheet = null;

function PostData( data, callback )
{
    data.investigator = _sheet.name;
    console.log( "PostData", data );
    
    fetch('https://fraser.epizy.com/Jonathan/character_sheet.php', {
        method: 'POST', // or 'PUT'
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(data),
    })
    .then(response => response.json())
    .then(data => {
        console.log('Success:', data);
        if( !!callback ) callback.call( this, data );
    })
    .catch((error) => {
        console.error('Error:', error);
        if( !!callback ) callback.call( this, { checkboxes: {}, radios: {} } );
    });
}

function StartUp(sheet)
{
    _sheet = sheet;
    PostData( { action: 'read' }, DataReadSuccess );
}

function DataReadSuccess(data)
{
    _state = data;
    BuildPage();
}

function RadioClicked( radio )
{
    var data = { saveType: 'radio', saveName: radio.name, saveValue: parseInt(radio.value) };
    
    if( _state.radios[ radio.name ] === undefined )
        data.action = "save";
    else
        data.action = "update";
        
    PostData( data );
    _state.radios[ radio.name ] = radio.value;

    if( radio.name === "flesh-ward-value-group" )
    {
       UpdateFleshWard(data.saveValue);
    }
}

function UpdateFleshWard(value)
{
    let content = "";
    if( value !== 0 ) content = AllNumbers( value, 0, "flesh-ward-value", "Flesh Ward" );

    let rangeHolder = document.getElementById("flesh-ward");
    rangeHolder.innerHTML = content;

    const currentValue = document.getElementById("flesh-ward-current");
    currentValue.innerHTML = value.toString(); 

    const buttons = document.querySelectorAll('button[data-spell-name="Flesh Ward"]');
    buttons.forEach(btn => { btn.disabled = (value !== 0); });
}

function CheckboxChanged( checkbox )
{
    var data = { saveType: 'checkbox', saveName: checkbox.id, saveValue: !!checkbox.checked };
    
    if( _state.checkboxes[ checkbox.id ] === undefined )
        data.action = "save";
    else
        data.action = "update";
        
    PostData( data );
    _state.checkboxes[ checkbox.id ] = checkbox.checked;
}
function SpellButtonClicked( button )
{
    const spellname = button.dataset.spellName;
    //console.log(spellname);

    const valuestring = prompt("What is the value of the Flesh Ward?\n(1D6 for each Magic Point spent)");
    const value = parseInt(valuestring);

    if( !Number.isNaN(value)) RadioClicked({ "name": "flesh-ward-value-group", "value": valuestring });
}

function RadioValue( name )
{
    return( _state.radios[ name ] );
}
function CheckboxValue( name )
{
    return( !!_state.checkboxes[ name ] );
}

function AllValues(inObject, cssClass, title, exclude, onlyInclude)
{
    exclude = exclude || [];
    onlyInclude = onlyInclude || [];
    let hasValues = false;

    let html = "";
    if( title ) html += '<div class="title">' + title + '</div>';
    
    for( var param in inObject )
    {
        var addParam = !( exclude.includes( param ) );
        if( addParam && onlyInclude.length ) addParam = onlyInclude.includes( param );
        
        if( addParam )
        {
            var titleInsert = "";
            var caption = param;
            
            if( ( cssClass == "stats" || cssClass == "fixedStats" ) && param.length == 3 && param != "SAN" )
            {
                titleInsert = ' title="5 x ' + param + ' = ' + ( inObject[param] * 5 ) + '"';
            }
            else if( cssClass == "fixedStats" && param.includes(' ') )
            {
                titleInsert = ' title="' + param + '"';
                caption = param.split(' ')[0];
            }

            const value = inObject[param] || "";
            html += "<div " + titleInsert + "><span>" + caption + "</span><span>" + value.toString().replace(/\n/gi, '<br />') + "</span></div>";

            if( value ) hasValues = true;
        }
    }

    let hideClass = (!hasValues ? " hideScreen" : "");

    html = '<div class="' + cssClass + hideClass + '">' + html + "</div>";
    return( html );
}

function AllNumbers(max, bonus, cssClass, title, isZero, minValue, itemName )
{
    var html = '<div class="numbers ' + cssClass + '">';
    
    if( !!title) html += '<div class="title">' + title + '</div>';
    if( !!isZero) html += '<span class="highlight">' + isZero + '</span>';
    
    minValue = minValue || 0;

    if (!itemName ) itemName = cssClass;
    
    var groupName = itemName + "-group";
    var value = RadioValue( groupName );
    if( value === undefined ) value = (max + bonus); 
    
    for( var i = minValue; i <= (max + bonus); i++ )
    {
        var id = itemName + "-" + i;
        
        var checked = "";
        if( i == value ) checked = ' checked="checked"';
        
        html += '<input type="radio" name="' + groupName + '" id="' + id + '" onclick="RadioClicked(this);" value="' + i + '"' + checked + ' />';
        html += '<label for="' + id + '"';
        
        if ( i <= Math.abs(minValue ) ) html += ' class="highlight"';
        if ( i == max || i == ( max + bonus ) ) html += ' class="highlight"';
        
        html += '>' + i + '</label>';
    }
    
    html += "</div>";
    
    //if ( cssClass == "shotgun" ) console.log( html );
    return( html );
}

function Pad( value )
{
    return( ( "00" + value).slice(-2) );
}

function FleshWardButton(caption, current)
{
    const id = "flesh-ward-" + caption.replace(/[^a-z0-9]/gi, "") + "-button";
    const dsbld = ( current === undefined || current === 0 ? "" : "disabled=\"true\" " );

    return '<div class="hide-print">' + 
        '<button ' + 
            'id="' + id + '" ' + 
            'type="button" ' + 
            dsbld + 
            'onclick="SpellButtonClicked(this);" ' +
            'data-spell-name="Flesh Ward">' + 
               caption + 
        '</button>' + 
    '</div>';
}

function TitleAndValue(title, content, cssClass)
{
    const classInsert = ( !content ? " hideScreen" : ""); 

    let html = '<div class="' + cssClass + classInsert + '">';
    html += '<div class="title">' + title + '</div>';
    html += '<span>';
    html += content;
    html += '</span>';
    html += '</div>';

    return html;
}

function BuildPage( data )
{
    var content = '';
    var tabs = [ "Details", "Sanity", "Combat", "Skills", "Magic", "More" ];

    content += '<div class="tabs" >';

    content += '<div class="whoami">';
    if( _sheet.image )
    {
        content += '<img src="' + _sheet.image + '"/>';
    }

    with( _sheet.investigatorName )
    {
        content += '<span>' + first + " " + last + '</span>';
    }
    content += '</div>';
    
    tabs.forEach( function( tab, i ) {
        content += '<a onclick="window.location.replace(\'#tab-' + tab.toLowerCase() + '\'); return false;">' + tab + '</a>';
    });
    
    content += '</div>';

    content += '<div class="wrapper">';
    content += '<div id="tab-details" class="tab-anchor">';
    content += '</div>';

    with( _sheet.investigatorName )
    {
        content += '<div class="name">' + title + " " + first + " " + middle + " " + last + '</div>';
    }

    content += AllValues( _sheet.details, "details" );
    content += AllValues( _sheet.stats, "stats", "", [ "Initiative", "Actions Per Round", "Damage Bonus" ] );
    content += AllValues( _sheet.stats, "moreStats", "", [], [ "Initiative", "Actions Per Round", "Damage Bonus" ] );

    if( _sheet.image )
    {
        content += '<img class="photo" src="' + _sheet.image + '"/>';
    }		
    
    content += AllValues( _sheet.stats, "fixedStats" );
    
    // -----
    
    //*
    content += '<div id="tab-sanity" class="tab-anchor">';
    content += '</div>';

    content += AllNumbers( _sheet.sanityPoints, 0, "sanity-points", "Sanity Points", "Insane" );

    if( _sheet.mentalDisorders || _sheet.episodesOfInsanity )
    {
        content += "<div class='moreStats sanity'>";
        if( _sheet.mentalDisorders ) content += "<div><span>Mental Disorders</span><span>" + _sheet.mentalDisorders + "</span></div>";
        if( _sheet.episodesOfInsanity ) content += "<div><span>Episodes of Insanity</span><span>" + _sheet.episodesOfInsanity + "</span></div>";
        content += "</div>";
    }
    //*/
    
    // -----
    
    //*
    content += '<div id="tab-combat" class="tab-anchor">';
    content += '</div>';

    content += AllNumbers( _sheet.hitPoints, 0, "hit-points", "Hit Points", "Dead", -2 );

    content += "<div id='flesh-ward' class='hide-print'>";

    const fleshWard = RadioValue("flesh-ward-value-group");
    if( fleshWard !== undefined && fleshWard !== 0 )
        content += AllNumbers( Number(fleshWard), 0, "flesh-ward-value", "Flesh Ward" );
    content += "</div>";

    content += FleshWardButton("+ Flesh Ward", fleshWard);
    
    content += "<div class='weapons'><table>";
    content += "<tr><th>Melee</th><th>%</th><th>Damage</th></tr>";
    
    let db = _sheet.stats["Damage Bonus"];
    if( db === "0" ) db = "";

    _sheet.weapons.melee.forEach( function(weapon) {
    
        var id = "melee-" + weapon.name.replace(/\s/gi, "");
        
        var checked = "";
        if( CheckboxValue( id ) == true )
        {
            checked = ' checked="checked"';
        }

        content += "<tr><td>";
        content += '<input type="checkbox" id="' + id + '" onchange="CheckboxChanged(this);"' + checked + ' />';
        content += '<label for="' + id + '">' + weapon.name + "</label>";
        content += "</td><td>" + weapon.skill + "</td><td>" + weapon.damage.replace("+db", db) + "</td></tr>";
    
    });
    
    content += "</table>";
    content += "<table>";
    content += "<tr><th>Firearm</th><th>%</th><th>Damage</th><th>Shots</th><th>Base Range</th></tr>";
    
    _sheet.weapons.firearm.forEach( function(weapon) {

        var id = "firearm-" + weapon.name.replace(/\s/gi, "");

        var checked = "";
        if( CheckboxValue( id ) == true )
        {
            checked = ' checked="checked"';
        }

        content += "<tr><td>";
        content += '<input type="checkbox" id="' + id + '" onchange="CheckboxChanged(this);"' + checked + ' />';
        content += '<label for="' + id + '">' + weapon.name + "</label>";
        content += "</td><td>" + weapon.skill + "</td><td>" + weapon.damage + "</td><td>" + weapon.shots + "</td><td>" + weapon.range + "</td></tr>";
    
    });
    content += "</table></div>";
    
    var haveShots = _sheet.weapons.firearm.filter( function( w ) { return( !!w.shots ); });
    haveShots.forEach( function(weapon) {
        /*
        var nameParts = weapon.name.split( " " );
        content += AllNumbers( weapon.shots, 0, nameParts[0].toLowerCase(), nameParts[0] + " - Shots" );
        */

        //var nameParts = weapon.name.split( " " );
        content += AllNumbers( weapon.shots, 0, "weapon", weapon.name + " - Shots", null, null, weapon.name.replace(/[^a-z0-9]/gi, "-").toLowerCase() );
        

    });
    //*/
    
    // -----
    
    //*
    content += '<div id="tab-skills" class="tab-anchor">';
    content += '</div>';
    
    content += "<div class='skills'>";
    content += '<div class="title">Skills</div>';
    
    for( var skill in _sheet.skills )
    {
        var cssClass = "";
    
        var value = _sheet.skills[ skill ];
        if( value > 25 ) cssClass = "highlight";
        
        var id = "skill-" + skill.replace(/\s/gi, '');
        
        var checked = "";
        if( CheckboxValue( id ) == true )
        {
            checked = ' checked="checked"';
        }
        
        content += "<div class='" + cssClass + "'>";
        content += '<input type="checkbox" id="' + id + '" onchange="CheckboxChanged(this);"' + checked + ' />';
        content += '<label for="' + id + '">'
        content += "<span>" + skill + "</span><span>" + Pad( value ) + "%</span>";
        content += "</label>";
        content += "</div>";
    }
    content += "</div>";
    //*/
    
    // -----
    
    //*
    content += '<div id="tab-magic" class="tab-anchor">';
    content += '</div>';

    content += AllNumbers( _sheet.magicPoints, _sheet.magicPointBonus, "magic-points", "Magic Points", "Unconscious" );
    
    _sheet.spells.forEach( function( spellName ) {
    
        var spell = _spells.find( s => s.name == spellName );
        
        if( spell !== undefined )
        {
            content += '<div class="spell" >';
            content += '<div class="title">' + spell.name + '</div>';
            content += '<ul>';
                if( !!spell.cost ) content += '<li><span>Cost: </span><span>' + spell.cost + '</span></li>';
                if( !!spell.castingTime ) content += '<li><span>Casting Time: </span><span>' + spell.castingTime + '</span></li>';
                if( !!spell.chance ) 
                {
                    content += '<li><span>Chance: </span><span>' + spell.chance;
                    if( spell.chance == "Dreamwalk skill" ) {
                        content += " (" + _sheet.skills["Dreamwalk"] + "%)";
                        if ( spell.levels ) {
          let ch = _sheet.skills["Dreamwalk"];
          let lvl = spell.levels.find(l => l.from < ch && l.to >= ch);
          if (lvl) {
              content += '</span></li>';
              content += '<li><span>Level: </span><span>' + lvl.description;
          }
                        }
                    }
                    
                    content += '</span></li>';
                    // "Dreamwalk skill"
                }

                if( spellName === "Flesh Ward" )
                {
                    content += "<li class='hide-print'><span>Current Value: </span><span id='flesh-ward-current'>" + (fleshWard || "0") + "</span></li>";
                }
    
            content += '</ul>';
            content += '<div class="description">';

            if( !Array.isArray( spell.description ) ) spell.description = [ spell.description ];
            spell.description.forEach( function( desc ) { content += '<span>' + desc + '</span>'; });

            content += '</div>';
            if( spellName === "Flesh Ward" )
            {
                //const dsbld = ( fleshWard === undefined || fleshWard === 0 ? "" : "disabled=\"true\"" );
                //content += '<div class="hide-print"><button id="flesh-ward-button" type="button" ' + dsbld + ' onclick="SpellButtonClicked(this);" data-spell-name="' + spellName + '">Cast</button></div>';
                content += FleshWardButton("Cast", fleshWard);
            }
        content += "</div>";
        }
    });

    // -----

    content += '<div id="tab-more" class="tab-anchor">';
    content += '</div>';

    if( _sheet.adjustments )
    {
        content += '<div class="history personal income finances"><div class="title">Adjustments</div>';
        _sheet.adjustments.forEach((adj) => {
            let change = (adj.value < 0 ? adj.value : "+" + adj.value);
            if (adj.to === "magicPoints") change += " Magic Point" + (Math.abs(adj.value) === 1 ? "" : "s");

            let suffix = "";
            if (adj.isPermament) suffix = " (Permament)";

            content += `<div><span>${adj.reason}</span><span>${change}${suffix}</span></div>`;
        });
        content += '</div>';
    }

    /*
    content += '<div class="history"><div class="title">History</div>';
    content += '<div id="test-editor-history" class="editor-elements" data-caption="History..." data-property="history"></div>';
    content += '</div>';
    
    content += '<div class="history"><div class="title">Possessions</div>';
    content += '<div id="test-editor-possessions" class="editor-elements" data-caption="Possessions..." data-property="possessions"></div>';
    content += '</div>';
    
    content += '<div class="history"><div class="title">Finances</div>';
    content += '<div id="test-editor-finances" class="editor-elements" data-caption="Finances..." data-property="income"></div>';
    content += '</div>';
    */

    /*
    if ( _sheet.personalData )
    {
        content += AllValues( _sheet.personalData, "history personal income", "Personal Data" );
    }
    */
    content += AllValues(_sheet.personalData || {}, "history personal income finances", "Personal Data" );

    content += TitleAndValue("Possessions", _sheet.possessions.join('<br />'), "history possessions")
    /*
    if ( _sheet.possessions && _sheet.possessions.length )
    {
        content += '<div class="history possessions"><div class="title">Possessions</div><span>';
        //content += _sheet.possessions.reduce((a, p) => a + p + '<br />', '');
        content += _sheet.possessions.join('<br />');
        content += '</span></div>';
    }
    */

    content += AllValues(_sheet.income || {}, "history income finances", "Finances" );
    /*
    if ( _sheet.income )
    {
        content += AllValues( _sheet.income, "history income finances", "Finances" );
    }
    */

    content += TitleAndValue("History", _sheet.history.map(h => '<p>' + h + '</p>').join(''), "history")

    /*
    if ( _sheet.history )
    {
        if (!Array.isArray(_sheet.history)) _sheet.history = [ _sheet.history ];

        if ( _sheet.history.length )
        {
            content += '<div class="history"><div class="title">History</div><span>';
            //content += _sheet.history.reduce((a, p) => a + '<p>' + p + '</p>', '');
            content += _sheet.history.map(h => '<p>' + h + '</p>').join('');
            content += '</span></div>';
        }
    }
    */
    
    document.getElementById( "holder" ).innerHTML = content;
    InitialiseEditors();
}

var elems = null;
var editors = null;

function InitialiseEditors()
{
    elems = document.getElementsByClassName("editor-elements");
    editors = [];
    console.log( 'Editors: ' + elems.length );
    
    Array.prototype.forEach.call( elems, function(elem, i) {
        
        var ph = elem.dataset.caption || "Enter the text...";
        var textName = elem.dataset.property;
        var initData = null;
        
        if( _state[ textName ] !== undefined )
        {
            initData = JSON.parse( _state[ textName ] );
        }
        
        //console.log( textName );
        //console.log( _state[ textName ] );

        var editor = new EditorJS({ 
            holder: elem.id,
            minHeight: 0,
            inlineToolbar: ['bold', 'italic'],
            tools: { delimiter: Delimiter },
            onChange: function() { SaveEditor( i ); },
            placeholder: ph,
            data: initData
        });
        
        editors.push( editor );
        
    });
}
    
function SaveEditor( editorIndex )
{
    console.log( "Save Editor " + editorIndex );
    
    editors[editorIndex].save().then((output) => { 
        var textName = elems[editorIndex].dataset.property;
        var textValue = JSON.stringify(output);
        
        console.log( textName );
        console.log( output ); 
        
        var data = { 
            saveType: 'text', 
            saveName: textName, 
            saveText: textValue 
        };
        
        if( _state[ textName ] === undefined )
            data.action = "save";
        else
            data.action = "update";
            
        PostData( data );
        _state[ textName ] = textValue;				
        
    });
}
