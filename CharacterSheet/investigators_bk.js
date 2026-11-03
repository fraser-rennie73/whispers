const _investigators = [

    { 

        name: "michael",
        investigatorName: { title: "", first: "Michael", middle: "", last: "Thompson" },
        image: "Images/michael.jpg",
    
        details: 
        {
            "Occupation": "Police Officer (Retired)",
            "Birthplace": "Manchester, England",
            "Sex": "Male",
            "Age": 55
        },
        stats: 
        {
            STR: 9,  DEX: 13, INT: 12, Idea: null,
            CON: 13, APP: 8,  POW: 15, Luck: null,
            SIZ: 15, SAN: null, EDU: 19, Know: null,
            "Initiative": "1+1D6",
            "Actions Per Round": 2,
            "Damage Bonus": null
        },
        
        sanityPoints: null,
		mentalDisorders: "Dendrophobia",
        magicPoints: null,
        magicPointBonus: 0,
        hitPoints: null,

		adjustments: 
		[
			{ orderIndex: 1, to: "magicPoints", value: -1, reason: "Curse Of Leng", isPermament: true },
			{ orderIndex: 2, to: "magicPoints", value: 2, reason: "Sons of Monas Pendant", isPermament: false },
		],
        
        skills:
        {
            "Accounting": 10,
            "Anthropology": 1,
            "Archaeology": 1,
            "Art": 5,
            "Astronomy": 1,
            "Bargain": 5,
            "Biology": 1,
            "Camouflage": 28,
            "Chemistry": 1,
            "Climb": 41,
            "Conceal": 15,
            "Craft": 5,
            "Credit Rating": 15,
            "Cthulhu Mythos": 30,
            "Dreamwalk": 38,
            "Disguise": 1,
            "Dodge": 41,
            "Drive Auto": 42,
            "Electr. Repair": 10,
            "Fast Talk": 35,
            "First Aid": 67,
            "Geology": 1,
            "Hide": 28,
            "History": 20,
            "Jump": 25,
            "Law": 45,
            "Library Use": 30,
            "Listen": 41,
            "Locksmith": 1,
            "Make Maps": 12,
            "Martial Arts": 1,
            "Mech. Repair": 20,
            "Medicine": 5,
            "Natural Hist.": 10,
            "Navigate": 10,
            "Occult": 5,
            "Opr. Hvy. Mch.": 1,
            "Other Language": 1,
            "Own Language": 95,
            "Persuade": 40,
            "Pharmacy": 1,
            "Photography": 10,
            "Physics": 1,
            "Pilot": 1,
            "Psychoanalysis": 1,
            "Psychology": 54,
            "Ride": 5,
            "Sense Magic": 45,
            "Sneak": 43,
            "Spot Hidden": 61,
            "Swim": 25,
            "Throw": 25,
            "Track": 15,
            "Winter Survival": 32
        },
        weapons:
        {
            melee: [
                { name: "Fist", "skill": 55, "damage": "1D3+db" },
                { name: "Grapple", "skill": 45, "damage": "special" },
                { name: "Head", "skill": 10, "damage": "1D4+db" },
                { name: "Kick", "skill": 25, "damage": "1D6+db" },
                { name: "Nightstick", "skill": 58, "damage": "1D6+db" },
                { name: "Knife", "skill": 25, "damage": "" }
            ],
            firearm: [
                { name: "Handgun (.38)", "skill": 71, "damage": "1D10", "shots": 6, "range": "15 yards" },
                { name: "Shotgun", "skill": 59, "damage": "4/2/1D6", "shots": 5, "range": "10/20/50 yds" },
                { name: "Rifle (.303 Lee Enfield)", "skill": 44, "damage": "2D6+4", "shots": 10, "range": "90 yards" },
                { name: "Machine Gun", "skill": 15, "damage": "", "shots": "", "range": "" },
                { name: "SMG", "skill": 15, "damage": "", "shots": "", "range": "" }				
            ]
        },
        hints:
        {
            "Shock": "&frac12; Hit Points - roll &lt;CON with 1D20.",
            "Aim": "+10% per Action.",
            "Aim for Particular": "&frac14; Chance.",
            "Point Blank": "DEX in feet - 13 feet - double chance.",
            "Critical": "10% of Skill - roll damage twice."
        },
		personalData:
		{
			"Residence": "Vinegar Hill, Brooklyn",
			"Personal Description": "6'2\", broad shouldered, white hair and a wide flat nose",
			"Family &amp; Friends": "Wife: Margaret (deceased), Son: Christopher, lives in Oklahoma City"
		},
		income:
        {
            "Income": "$210/year - police retirement fund\n$80/month - odd jobs/security work",
            "Savings": "$1120",
            "Real Estate": "2 bed house in Vinegar Hill, Brooklyn (worth about $3000)."
        },
        possessions:
        [
            "SOM Pendant",
            "~ +2 Magic Points",
            "~ Sense Magic (2xPOW)",
            "",
            "Satchel",
            "~ Box of Handgun Ammo. (x50)",
            "~ 2 x Spare (loaded) Shotgun Magazines",
            "~ Compass",
            "~ Torch (and Spare Battery)",
            "~ Nightstick",
            "~ Crowbar",
            "~ Pen Knife",
            "~ First-Aid Kit",
            "~ Matches",
            "",
            "Binoculars",
            //"Winter Stone",
            //"Small tin of Black Dust (Meredith&apos;s)",
            "",
            "Winchester 97 12 Gauge Shotgun (5 shots)",
            ".38 Revolver Handgun (6 shots)",
            ".303 Lee Enfield Rifle (10 shots)"
        ],
        spells: 
        [
            "Heal", "Create Mist", "Flesh Ward", "Dreamwalk"   //, "Dreamsee"
        ],
        history: 
        [
            "Michael was born on 9th September 1879 to Michael and Mary Thompson, in Manchester England. Two years later his younger brother James was born, and in 1884 the Thompson family emigrated to New York. The family thrived in America. The Boys did well in school, Michael Snr. worked as a clerk in an Insurance office and Mary was a housewife.",
            "In 1902 Michael joined the New York Police Department. A year later he married Margaret and bought a house in Vinegar Hill, Brooklyn. One year after that, in 1904, Michael and Margaret had a son, Steven.",
            "In 1927, after 25 years in the NYPD, Michael retired. That same year Margaret died of a heart attack. Now Michael lives alone. Steven is a Police Officer in Oklahoma City, both his parents are dead and his brother lives in Los Angeles. He has no contact with his son or his brother. The last time he spoke to either of them was Margaret's funeral.",
            "The visions given to him by the Monolith and the Winter Stone have prompted Michael to get in contact with his son. He started by confirming that he wasn't dead on the Orient Express and they have now exchanged a couple of letters each and passed on their news. Michael has let his son know that he has a new job and is going be travelling, so he will be away for a while. He did not mention the true nature of the work."
        ]
    },
    {  
	
		name: "leonora",
		investigatorName: { title: "", first: "Leonora", middle: "", last: "Davidson" },
		image: "Images/leonora.jpg",
	
		details: 
		{
			"Occupation": "Private Detective",
			"Birthplace": "New York, NY",
			"Sex": "Female",
			"Age": 32
		},
		stats: 
		{
			STR: 10, DEX: 11, INT: 14, Idea: null,
			CON: 13, APP: 16, POW: 13, Luck: null,
			SIZ: 15, SAN: null, EDU: 15, Know: null,
			"Initiative": "1+1D6",
			"Actions Per Round": 3,
			"Damage Bonus": null
		},
		
		sanityPoints: null,
		magicPoints: null,
		magicPointBonus: 0,
		hitPoints: null,

		adjustments: 
		[
			{ orderIndex: 1, to: "magicPoints", value: 2, reason: "Sons of Monas Pendant", isPermament: false },
		],
		
		skills:
		{
			"Accounting": 10,
			"Anthropology": 1,
			"Archaeology": 1,
			"Art": 5,
			"Astronomy": 1,
			"Bargain": 5,
			"Biology": 1,
			"Chemistry": 1,
			"Climb": 41,
			"Conceal": 15,
			"Craft": 5,
			"Camouflage": 27,
			"Credit Rating": 15,
			"Cthulhu Mythos": 10,
			"Disguise": 54,
			"Dodge": 27,
			"Drive Auto": 60,
			"Dreamwalk": 24,
			"Electr. Repair": 10,
			"Fast Talk": 60,
			"First Aid": 54,
			"Geology": 1,
			"Hide": 12,
			"History": 25,
			"Jump": 25,
			"Law": 38,
			"Library Use": 40,
			"Listen": 47,
			"Locksmith": 1,
			"Make Maps": 12,
			"Martial Arts": 30,
			"Mech. Repair": 20,
			"Medicine": 5,
			"Natural Hist.": 10,
			"Navigate": 10,
			"Occult": 5,
			"Opr. Hvy. Mch.": 1,
			"Other Language": 1,
			"Own Language": 75,
			"Persuade": 40,
			"Pharmacy": 1,
			"Photography": 10,
			"Physics": 1,
			"Pilot": 1,
			"Psychology": 34,
			"Ride": 5,
			"Sense Magic": 38,
			"Sneak": 31,
			"Spot Hidden": 60,
			"Swim": 25,
			"Throw": 25,
			"Track": 13,
			"Winter Survival": 29
		},
		weapons:
		{
			melee: [
				{ name: "Fist", "skill": 50, "damage": "1D3+db" },
				{ name: "Grapple", "skill": 31, "damage": "special" },
				{ name: "Head", "skill": 10, "damage": "1D4+db" },
				{ name: "Kick", "skill": 31, "damage": "1D6+db" }
			],
			firearm: [
				{ name: "Handgun (Colt)", "skill": 43, "damage": "1D10", "shots": 6, "range": "15 yards" },
				{ name: "Shotgun", "skill": 30, "damage": "4/2/1D6", "shots": "", "range": "10/20/50 yds" },
				{ name: "Rifle", "skill": 31, "damage": "", "shots": "", "range": "" },
				{ name: "Machine Gun", "skill": 15, "damage": "", "shots": "", "range": "" },
				{ name: "SMG", "skill": 15, "damage": "", "shots": "", "range": "" }
			]
		},
		hints:
		{
			"Shock": "&frac12; Hit Points - roll &lt;CON with 1D20.",
			"Aim": "+10% per Action.",
			"Aim for Particular": "&frac14; Chance.",
			"Point Blank": "DEX in feet - 13 feet - double chance.",
			"Critical": "10% of Skill - roll damage twice."
		},
		personalData:
		{
			"Residence": "",
			"Personal Description": "",
			"Family &amp; Friends": ""
		},
		income:
        {
            "Income": "",
            "Savings": "",
            "Real Estate": ""
        },
		possessions:
		[
			"Colt Revolver (6 shots)",
			"",
			"SOM Pendant",
			"~ +2 Magic Points",
			"~ Sense Magic (2xPOW)",
			"",
			"Hawkskull"
		],
		history: [
			"Leonora Davidson was born in NYC in 1902. She is an only child of a New York cop and a stenographer. She was trained to be a secretary and joined the Bureau of Investigation as a secretary but her talents as an undercover agent were quickly noticed."
		],
		spells: 
		[
			"Heal", "Flesh Ward", "Create Mist", "Dreamwalk"
		]
	},
    {  
	
		name: "rosie",
		investigatorName: { title: "", first: "Judith", middle: "'Rosie'", last: "Rosen" },
		image: "Images/rosie.jpg",
	
		details: 
		{
			"Occupation": "Reporter",
			"Birthplace": "Boston, MA",
			"Sex": "Female",
			"Age": 25
		},
		stats: 
		{
			STR: 11, DEX: 12, INT: 15, Idea: null,
			CON: 16, APP: 11, POW: 15, Luck: null,
			SIZ: 9, SAN: null, EDU: 14, Know: null,
			"Initiative": "1+1D6",
			"Actions Per Round": 3,
			"Damage Bonus": null
		},
		
		sanityPoints: null,
		magicPoints: null,
		magicPointBonus: 0,
		hitPoints: null,

		adjustments: 
		[
			{ orderIndex: 1, to: "magicPoints", value: 2, reason: "Sons of Monas Pendant", isPermament: false },
		],
		
		skills:
		{
			"Accounting": 10,
			"Anthropology": 1,
			"Archaeology": 1,
			"Art": 5,
			"Astronomy": 1,
			"Bargain": 5,
			"Biology": 1,
			"Chemistry": 1,
			"Climb": 50,
			"Conceal": 15,
			"Craft": 5,
			"Credit Rating": 25,
			"Cthulhu Mythos": 20,
			"Dodge": 50,
            "Dreamwalk": 44,
			"Drive Auto": 30,
			"Electr. Repair": 10,
			"Fast Talk": 25,
			"First Aid": 47,
			"Geology": 1,
			"Hide": 10,
			"History": 20,
			"Jump": 40,
			"Law": 40,
			"Library Use": 56,
			"Listen": 51,
			"Locksmith": 1,
			"Martial Arts": 1,
			"Mech. Repair": 20,
			"Medicine": 5,
			"Natural Hist.": 10,
			"Navigate": 10,
			"Occult": 5,
			"Opr. Hvy. Mch.": 1,
			"Other Language": 1,
			"Own Language": 70,
			"Persuade": 69,
			"Pharmacy": 1,
			"Photography": 10,
			"Physics": 1,
			"Pilot": 1,
			"Psychology": 15,
			"Ride": 5,
			"Sense Magic": 30,
			"Sneak": 15,
			"Spot Hidden": 56,
			"Swim": 45,
			"Throw": 25,
			"Track": 25
		},
		weapons:
		{
			melee: [
				{ name: "Fist", "skill": 50, "damage": "1D3+db" },
				{ name: "Grapple", "skill": 25, "damage": "special" },
				{ name: "Head", "skill": 10, "damage": "1D4+db" },
				{ name: "Kick", "skill": 25, "damage": "1D6+db" }
			],
			firearm: [
				{ name: "Handgun (Colt)", "skill": 60, "damage": "1D10+2", "shots": 6, "range": "15 yards" },
				{ name: "Handgun (Deringer)", "skill": 60, "damage": "1D6", "shots": 6, "range": "15 yards" },
				{ name: "Shotgun", "skill": 30, "damage": "4/2/1D6", "shots": "", "range": "10/20/50 yds" },
				{ name: "Rifle", "skill": 25, "damage": "", "shots": "", "range": "" },
				{ name: "Machine Gun", "skill": 15, "damage": "", "shots": "", "range": "" },
				{ name: "SMG", "skill": 15, "damage": "", "shots": "", "range": "" }
			]
		},
		hints:
		{
			"Shock": "&frac12; Hit Points - roll &lt;CON with 1D20.",
			"Aim": "+10% per Action.",
			"Aim for Particular": "&frac14; Chance.",
			"Point Blank": "DEX in feet - 13 feet - double chance.",
			"Critical": "10% of Skill - roll damage twice."
		},
		personalData:
		{
			"Residence": "SOM",
			"Personal Description": "Dark hair, mousey, intense - tries to be stylish - takes info from sister but looks ridiculous.",
			"Family &amp; Friends": "Dad - lawyer, Sister - Polio / wheelchair"
		},
		income:
        {
            "Income": "",
            "Savings": "",
            "Real Estate": ""
        },
		possessions:
		[
			"SOM Pendant",
			"~ +2 Magic Points",
			"~ Sense Magic (2xPOW)",
			"",
			"Colt + ammo",
			"Torch",
			"Camera",
			"Notepad",
			"Pens",
			"Satchel",
			"Picture of Hollins (30s)",
			"",
			"Deringer .25 (6 shots)",
			"Colt Revolver (6 shots)"
		],
		history: [],
		spells: 
		[
			"Elder Sign", "Heal", "Create Mist", "Clutch of Nyogtha", "Flesh Ward", "Shrivelling"
		]
	}

	//*
	,
    {  
	
		name: "meredith",
		investigatorName: { title: "", first: "Meredith", middle: "", last: "Coren" },
		image: "Images/meredith.jpg",
	
		details: 
		{
			"Occupation": "Bartender",
			"College, Degrees": "Did not finish school",
			"Birthplace": "Upstate NY",
			"Sex": "Female",
			"Age": 20
		},
        stats: 
        {
            STR: 10,  DEX: 17, INT: 14, Idea: null,
            CON: 14, APP: 11,  POW: 16, Luck: null,
            SIZ: 11, SAN: null, EDU: 15, Know: null,
            "Initiative": "1+1D6",
            "Actions Per Round": 3,
            "Damage Bonus": null
        },
        
        sanityPoints: null,
		mentalDisorders: "None diagnosed",
		episodesOfInsanity: "Spent from age 16-18 in Arkham Asylum for truancy, hysteria, insubordination and unbecoming behaviour.",
        magicPoints: null,
        magicPointBonus: 0,
        hitPoints: null,

		adjustments: 
		[
			{ orderIndex: 1, to: "magicPoints", value: 2, reason: "Sons of Monas Pendant", isPermament: false },
		],
        
        skills:
        {
            "Accounting": 10,
            "Anthropology": 1,
            "Archaeology": 1,
            "Art": 5,
            "Astronomy": 1,
            "Bargain": 5,
            "Biology": 1,
            "Camouflage": 30,
            "Chemistry": 1,
            "Climb": 59,
            "Conceal": 35,
            "Craft": 5,
            "Credit Rating": 15,
            "Cthulhu Mythos": 15,
            "Dreamwalk": 44,
            "Disguise": 1,
            "Dodge": 59,
            "Drive Auto": 20,
            "Electr. Repair": 10,
            "Fast Talk": 5,
            "First Aid": 33,
            "Geology": 1,
            "Hide": 46,
            "History": 20,
            "Jump": 34,
            "Law": 5,
            "Library Use": 25,
            "Listen": 63,
            "Locksmith": 41,
            "Make Maps": 13,
            "Martial Arts": 1,
            "Mech. Repair": 30,
            "Medicine": 5,
            "Natural Hist.": 10,
            "Navigate": 10,
            "Occult": 5,
            "Opr. Hvy. Mch.": 1,
            "Other Language": 1,
            "Own Language": 75,
            "Persuade": 15,
            "Pharmacy": 1,
            "Photography": 10,
            "Physics": 1,
			"Pickpocket": 68,
            "Pilot": 1,
            "Psychoanalysis": 1,
            "Psychology": 52,
            "Ride": 5,
            "Sense Magic": 42,
            "Sneak": 71,
            "Spot Hidden": 60,
            "Swim": 25,
            "Throw": 25,
            "Track": 35,
            "Winter Survival": 25
        },
        weapons:
        {
            melee: [
                { name: "Fist", "skill": 50, "damage": "1D3+db" },
                { name: "Grapple", "skill": 25, "damage": "special" },
                { name: "Head", "skill": 10, "damage": "1D4+db" },
                { name: "Kick", "skill": 25, "damage": "1D6+db" },
                { name: "Knife", "skill": 28, "damage": "1D4+db" }
            ],
            firearm: [
                { name: "Handgun (.32)", "skill": 23, "damage": "1D6+1", "shots": 6, "range": "15 yards" },
                { name: "Handgun (.45)", "skill": 23, "damage": "1D10+2", "shots": 6, "range": "15 yards" },
                { name: "Rifle (.22)", "skill": 32, "damage": "1D6+2", "shots": 6, "range": "30 yards" },
                { name: "Shotgun", "skill": 30, "damage": "", "shots": "", "range": "" },
                { name: "Machine Gun", "skill": 15, "damage": "", "shots": "", "range": "" },
                { name: "SMG", "skill": 15, "damage": "", "shots": "", "range": "" }				
            ]
        },
        hints:
        {
            "Shock": "&frac12; Hit Points - roll &lt;CON with 1D20.",
            "Aim": "+10% per Action.",
            "Aim for Particular": "&frac14; Chance.",
            "Point Blank": "DEX in feet - 13 feet - double chance.",
            "Critical": "10% of Skill - roll damage twice."
        },
		personalData:
		{
			"Residence": "Hell's Kitchen, New York",
			"Personal Description": "Tall, lanky, slightly awkward, brown eyes, brown curly hair. Wears brown 3 piece suit and cap. Usually quiet, but with a smart mouth when provoked.",
			"Family &amp; Friends": "Mother, stepfather and brother (12), living in Buffalo, NY."
		},
        income:
        {
            "Income": "$6/week - plus accommodation above the bar",
            "Savings": "$287",
            "Real Estate": "None"
        },
        possessions:
        [
            "Amulet",
            "~ +2 Magic Points",
            "~ Sense Magic (2xPOW)",
            "",
			"Knife",
			"Lock picks",
			"Teddy bear",
			"",
            "Colt .32 Revolver Handgun (6 shots)",
            "Magnum .45 Handgun (6 shots)",
            ".22 Rifle (6 shots)"
        ],
        spells: 
        [
            "Shrivelling", "Create Mist", "Heal", "Dreamwalk"   //, "Dreamsee"
        ],
        history: 
        [
            "Brought up in a one parent household with little brother, started truanting when mother married her stepfather who she does not get on with.",
            "Truancy and bad behaviour led to being taken into care at various institutions where she was made a Ward of the Stsate until her 18th birthday.",
            "Due to poor relationship with stepfather she does not accept authority of males easily and is not a team player. Distrustful of strangers.",
            "Suppliments meagre income by pickpocketing customers and stealing from houses."
        ]
	},
    {  
	
		name: "henry",
		investigatorName: { title: "", first: "Henry", middle: "", last: "Fabyan" },
		image: "Images/henry.jpg",
	
		details: 
		{
			"Occupation": "Cryptologist",
			"Colleges, Degrees": "Mathematics",
			"Birthplace": "Boston, MA",
			"Sex": "Male",
			"Age": 41
		},
		stats: 
		{
			STR: 13, DEX: 14, INT: 16, Idea: null,
			CON: 14, APP: 12, POW: 17, Luck: null,
			SIZ: 14, SAN: null, EDU: 20, Know: null,
			"Initiative": "1+1D6",
			"Actions Per Round": 3,
			"Damage Bonus": null
		},
		
		sanityPoints: null,
		magicPoints: null,
		magicPointBonus: 0,
		hitPoints: null,

		adjustments: 
		[
			{ orderIndex: 1, to: "magicPoints", value: 2, reason: "Sons of Monas Pendant", isPermament: false },
		],
		
		skills:
		{
			"Accounting": 10,
			"Anthropology": 1,
			"Archaeology": 1,
			"Art": 5,
			"Astronomy": 1,
			"Bargain": 5,
			"Biology": 1,
			"Chemistry": 1,
			"Climb": 40,
			"Conceal": 15,
			"Craft": 5,
			"Credit Rating": 65,
			"Cthulhu Mythos": 27,
			"Dodge": 34,
            "Dreamwalk": 31,
			"Drive Auto": 20,
			"Electr. Repair": 49,
			"Fast Talk": 5,
			"First Aid": 36,
			"Geology": 1,
			"Hide": 10,
			"History": 20,
			"Jump": 25,
			"Law": 5,
			"Library Use": 85,
			"Listen": 25,
			"Locksmith": 1,
			"Martial Arts": 1,
			"Mathematics": 94,
			"Mech. Repair": 25,
			"Medicine": 5,
			"Natural Hist.": 10,
			"Navigate": 10,
			"Occult": 56,
			"Opr. Hvy. Mch.": 1,
			"Other Language": 1,
			"Own Language": 100,
			"Persuade": 35,
			"Pharmacy": 1,
			"Photography": 10,
			"Physics": 54,
			"Pilot": 1,
			"Psychology": 5,
			"Ride": 5,
			"Sense Magic": 34,
			"Sneak": 10,
			"Spot Hidden": 82,
			"Swim": 25,
			"Throw": 25,
			"Track": 10
		},
		weapons:
		{
			melee: [
				{ name: "Fist", "skill": 50, "damage": "1D3+db" },
				{ name: "Grapple", "skill": 25, "damage": "special" },
				{ name: "Head", "skill": 10, "damage": "1D4+db" },
				{ name: "Kick", "skill": 25, "damage": "1D6+db" }
			],
			firearm: [
				{ name: "Handgun (Walther)", "skill": 61, "damage": "1D8", "shots": 7, "range": "15 yards" },
				{ name: "Handgun (.45)", "skill": 55, "damage": "1D10+2", "shots": 7, "range": "15 yards" },
				{ name: "Shotgun", "skill": 30, "damage": "4/2/1D6", "shots": "", "range": "10/20/50 yds" },
				{ name: "Rifle", "skill": 25, "damage": "2D6+4", "shots": 10, "range": "" },
				{ name: "Machine Gun", "skill": 15, "damage": "", "shots": "", "range": "" },
				{ name: "SMG", "skill": 15, "damage": "", "shots": "", "range": "" }
			]
		},
		hints:
		{
			"Shock": "&frac12; Hit Points - roll &lt;CON with 1D20.",
			"Aim": "+10% per Action.",
			"Aim for Particular": "&frac14; Chance.",
			"Point Blank": "DEX in feet - 13 feet - double chance.",
			"Critical": "10% of Skill - roll damage twice."
		},
		personalData:
		{
			"Residence": "New York and Chicago",
			"Personal Description": "6'1\", long limbed, wide shouldered, heavy boned",
			"Family &amp; Friends": "Cousin of George Fabyan of Riverside, Chicago"
		},
		income:
        {
            "Income": "",
            "Savings": "",
            "Real Estate": ""
        },
		possessions:
		[
			"SOM Pendant",
			"~ +2 Magic Points",
			"~ Sense Magic (2xPOW)",
			"",
			"Fedora, brown suit jacket, light blue jumper, tweed trousers, stout leather boots",
			"Leather gloves",
			"Shoulder holster",
			"~ Walther PP",
			"Leather satchel",
			"~ 4 clips for Walther",
			"~ Torch",
			"~ Camera",
			"~ Notebook",
			"Luggage",
			"~ Colt .45",
			"~ 2 Clips for Colt"
		],
		history: [],
		spells: 
		[
			"Heal", "Shrivelling", "Flesh Ward", "Contact Cthonian"
		]
	}
	//*/

];

_investigators.forEach(sheet => {

	if( sheet.stats )
	{
		if( !sheet.skills["Cthulhu Mythos"] ) sheet.skills["Cthulhu Mythos"] = 0;

		sheet.stats.Idea = sheet.stats.INT * 5;
		sheet.stats.Luck = sheet.stats.POW * 5;
		sheet.stats.Know = sheet.stats.EDU * 5;
		sheet.stats.SAN = 99 - sheet.skills["Cthulhu Mythos"];

		const damage = sheet.stats.STR + sheet.stats.SIZ;
		let bonus = "";

		if( damage < 13 ) bonus = "-2";
		else if( damage < 18 ) bonus = "-1";
		else if( damage < 25 ) bonus = "0";
		else if( damage < 33 ) bonus = "+1D4";
		else bonus = "+1D6";

		sheet.stats["Damage Bonus"] = bonus;

		sheet.sanityPoints = Math.min( sheet.stats.POW * 5, sheet.stats.SAN );
		sheet.magicPoints = sheet.stats.POW;
		sheet.hitPoints = Math.ceil((sheet.stats.CON + sheet.stats.SIZ) / 2);

		if( sheet.adjustments )
		{
			sheet.adjustments.forEach((adj) => {
				if( adj.to === "magicPoints" && adj.isPermament ) {
					sheet.magicPoints += adj.value;
				}
				else if( adj.to === "magicPoints" && !adj.isPermament ) {
					sheet.magicPointBonus += adj.value;
				}
			});
		}
	}
});

function GetInvestigator(name)
{
    return _investigators.find(inv => inv.name === name);
} 
