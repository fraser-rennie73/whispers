	
	var sheet = {  
	
		name: "leonora",
	
		details: 
		{
			"Investigator Name": "Leonora Davidson",
			"Occupation": "Private Detective",
			"Birthplace": "New York, New York",
			"Sex": "Female",
			"Age": 32
		},
		stats: 
		{
			STR: 10, DEX: 11, INT: 14, Idea: 70,
			CON: 13, APP: 16, POW: 13, Luck: 65,
			SIZ: 15, SAN: 89, EDU: 15, Know: 75,
			"Initiative": "1+1D6",
			"Actions Per Round": 3,
			"Damage Bonus": "+1D4"
		},
		
		investigatorName: { title: "Miss", first: "Leonora", middle: "", last: "Davidson" },
		image: "Images/leonora.jpg",
		sanityPoints: 65,
		magicPoints: 13,
		magicPointBonus: 2,
		hitPoints: 14,
		
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
		history: "",
		spells: 
		[
			"Heal", "Flesh Ward", "Create Mist", "Dreamwalk"
		]
	};

	/*
				"Amulet",
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
			"Winter Stone",
			"Small tin of Black Dust (Meredith&apos;s)",
			"",
	*/

	var _spells = [

		{
			"name": "Heal",
			"cost": "3 magic points",
			"castingTime": "several minutes",
			"description": "Heals wounds. You must be able to touch the recipient. Heals 3 hit points or 6 if combined with full medical care. You can try to cast the spell quickly: it takes two combat rounds, the cost is 3 magic points, but success is not guaranteed and you must roll under 5 times your current Magic Points as a %. If successful, the recipient receives 1d3 hit points."
		},
		{
			"name": "Flesh Ward",
			"cost": "variable magic points, 1d4 sanity points",
			"castingTime": "5 combat rounds (roughly one minute)",
			"description": "Creates a magical protective coating on the recipient’s skin that absorbs damage for 24 hours. Every magic point spent provides 1d6 points of protection."
		},
		{
			"name": "Create Mist",
			"cost": "2 magic points",
			"castingTime": "1 combat round",
			"description": "Creates a thick mist roughly 10x10x15 feet in area which lasts for 1d6 combat rounds. Takes one combat round to cast. Costs 2 Magic Points (spending more Magic Points increases the area of the spell and the duration)."
		},
		{
			"name": "Dreamwalk",
			"cost": "1 magic point per hour, 1d3 sanity points",
			"chance": "Dreamwalk skill",
	
			"description": [
				"When in the dreamlands, the dreamself version of the character is essentially the same as the real character, with the same statistics, skill levels and spell knowledge. Actions, initiative and hit points all function normally. Equipment tends to be the same as what the real world character is wearing & carrying when they initiate the walk, although some objects can change during the transition.",
			],
			"additional": [
				"To deliberately escape the dreamlands and wake up, they must roll under their dreamwalk skill +40%. Regardless of their skill level, a roll between 96-100% is always a fail. Escaping the dreamlands in this fashion costs 2 Magic Points, and requires a full combat round of uninterrupted focus.",
				
				"Certain objects and materials, incluiding Black Dust, adds to the chance of success and to the level of skill for that dreamwalk. Black Dust will increase your chance by +1d4, +2d4 or +3d4, depending on the quantity taken.",
				
				"To travel to a particular dream place (other than the naturally corresponding part of the dreamlands) a character must have visited it before, or know/have a strong link to a dreaming person/object in that place. If they have been there before, success is based on their dreamwalk skill +20%; if through a connection to a person/object, skill +10%.",
	
				"If a character &apos;dies&apos; in the dreamlands, they must roll under 5 times their intelligence OR under their dreamwalk skill, whichever is higher. If they succeed, they wake up in the real world, if they fail, they are so immersed in the dreamwalk that dying there stops their heart and prompts cardiac arrest in the real world as well. Unless given immediate emergency medical treatment, the character will die.",
	
				"If a character goes insane in the dreamworld, the effects remain exactly the same when they awake in the real world, including permanent insanity.",
	
				"<b>Example</b>",
				"Howard the archaeologist has managed to get his dreamwalking skill up to 39%. He decides to consume a coffee laced with one pinch of the black dust (enough to help, adding +1d4 to his skill, but not enough to bring on hallucinations or addiction) and rolls a 3, bringing his skill up to 42% for the duration of this dreamwalk. Because he is visiting a place he has been to before, his chance of successfully initiating the dreamwalk is +20%, or 62%, a roll which he makes. ",
				"His skill level for the duration of this dreamwalk is still considered to be 42%, and the player consults the table below to see what that means. Howard wants to visit a temple in the dreamlands that he has been to before, which, in relation to the real world, is roughly 700 miles away. The table tells him he has a range of 1000 miles, so this is fine. He also gains +2d6 points for his dreamself statistics on this particular dreamwalk. He rolls 9, and decides that he will put 5 of these points on his Magic Points, as he knows he is likely to have to cast spells, 2 on his Strength, because he intends to open a heavy stone door while in the dreamlands, and 2 points on his Appearance because he enjoys the frisson of being better looking in the dreamlands.",
				"Howard appears at the temple, and successfully opens the stone door in a STR vs STR roll, but then is horrified to discover one of the vast Spawn of Cthulhu within. Losing sanity, but not enough to drive him insane, Howard recognises that his foe is far too powerful to tackle alone and he tries to wake himself up. His chance of waking himself is 42% +40%, or 82%. The player rolls 91%! Unable to immediately return to the real world, Howard flees the temple, but is quickly caught in the clutches of the Spawn which then uses magic to search Howard’s mind and learn where he lives in the real world. Despite his extra Magic Points, the dice are cruel and Howard is unable to resist the MP vs MP mind read. His thoughts interrogated, the Spawn crushes Howard in its great claw, ‘killing’ his dreamself. Howard successfully rolls under his Intelligence x5 and awakes in the real world, shaken and sweating. He then considers moving house."
			]
		},
		{
			"name": "Dreamsee",
			"cost": "2 magic points per hour, 1d3 sanity points",
			"chance": "Dreamwalk skill",
	
			"description": [
				"You get a hazy view of a place in the dreamlands, but your dreamself doesn’t travel there. It requires a prior &apos;connection&apos; to an object, place or person. You can still lose sanity, but cannot interact with the area viewed.",
			],
			"additional": [
				"You can dreamsee to a distance one level higher than your normal skill level (see Dreamwalk table).",
				"If attempting to dreamsee a person that person must be in the dreamlands at that point. The target will have a chance of sensing the attempt using their POW as a % or dreamwalking skill (whichever is higher). They can resist with a Magic Points vs Magic Points roll against the person attempting to dreamsee."
			]
		}];
	
		var _dreamwalk = {
	
			"name": "Dreamwalk",
			"cost": "1 magic point per hour, 1d3 sanity points",
			"chance": "27%",
	
			"description": [
				"To successfully initiate a dreamwalk, a player must roll under their character’s dreamwalk skill.",
				"When in the dreamlands, the dreamself version of the character is essentially the same as the real character, with the same statistics, skill levels and spell knowledge. Actions, initiative and hit points all function normally. Equipment tends to be the same as what the real world character is wearing & carrying when they initiate the walk, although some objects can change during the transition.",
				
				"To deliberately escape the dreamlands and wake up, they must roll under their dreamwalk skill +40%. Regardless of their skill level, a roll between 96-100% is always a fail. Escaping the dreamlands in this fashion costs 2 Magic Points, and requires a full combat round of uninterrupted focus.",
				
				"Black dust adds to the chance of success, and to the level of skill for that dreamwalk, by either +1d4, +2d4 or +3d4, depending on the quantity taken. Other objects and materials can aid dreamwalking as well.",
				
				"To travel to a particular dream place (other than the naturally corresponding part of the dreamlands) a character must have visited it before, or know/have a strong link to a dreaming person/object in that place. If they have been there before, success is based on their dreamwalk skill +20%; if through a connection to a person/object, skill +10%.",
				"It is also possible to &apos;dreamsee&apos; where the character gets a hazy view of a place in the dreamlands, but their dreamself doesn’t travel there. This always requires a prior ‘connection’ to an object, place or person (if that person is in the dreamlands at that point). It requires a successful dreamwalk skill roll, and costs 2MP/hour to dreamsee, and 1d3 Sanity. A person can dreamsee to a distance one level higher than their normal skill level (see table below). A character can still lose sanity when dreamseeing, but cannot interact with the area viewed. If attempting to dreamsee a person, that person will have a chance of sensing the attempt (their Power as a %, or dreamwalking skill if they have it, whichever is higher). If they become aware and do not wish to be dreamseen they can resist with a Magic Points vs Magic Points roll against the person attempting to dreamsee. ",
	
				"If a character &apos;dies&apos; in the dreamlands, they must roll under 5 times their intelligence OR under their dreamwalk skill, whichever is higher. If they succeed, they wake up in the real world, if they fail, they are so immersed in the dreamwalk that dying there stops their heart and prompts cardiac arrest in the real world as well. Unless given immediate emergency medical treatment, the character will die.",
	
				"If a character goes insane in the dreamworld, the effects remain exactly the same when they awake in the real world, including permanent insanity.",
	
				"Spells work as normal in the dreamlands, and there are also spells that work only in the dreamlands, and can be learned in the usual fashion.",
				"As a character improves their dreamwalking skill, their dreamself becomes more powerful, with more Magic Points (MP) than the real character (see table below). Once someone has 31% dreamwalking skill or higher, they can choose to spend these extra points not just on Magic Points, but on any statistics except Power or Education. So a character can chose to improve: Strength, Constitution, Size, Intelligence, Dexterity and Appearance, with all the benefits those bring to initiative, actions, hit points etc. Each dreamwalk is different, so these bonuses are rolled each time, meaning that the abilities of a dreamwalker may vary from dream to dream."
			],
			//Dreamwalk skill level:
			"skillLevels": [
				{ "level": "01-10%", "skill": "Travel up to a mile" },
				{ "level": "11-20%", "skill": "Travel up to 10 miles, +1d3 MP" },
				{ "level": "21-30%", "skill": "Travel up to 100 miles, +1d6 MP" },
				{ "level": "31-40%", "skill": "Travel up to 1000 miles, +1d10 MP, MPs can be spent on other statistics" },
				{ "level": "41-50%", "skill": "Travel up to 10,000 miles, +2d6 MP, MPs can be spent on statistics" },
				{ "level": "51-60%", "skill": "Travel up to 1 million miles, +2d10 MP, MPs can be spent on statistics" },
				{ "level": "61-70%", "skill": "Travel up to 100 million miles, +3d6 MP, MPs can be spent on statistics" },
				{ "level": "71-80%", "skill": "Travel up to 100 light years, +3d10 MP, MPs can be spent on statistics" },
				{ "level": "81-90%", "skill": "Travel up to 100 million light years, +4d10 MP, MPs can be spent on statistics" },
				{ "level": "91-99%", "skill": "Travel up to 1 billion light years, +5d10 MP, MPs can be spent on statistics" },
				{ "level":   "100%", "skill": "Travel any distance, +6d10 MP, MPs can be spent on statistics" }
			],
			"example": [
				"Howard the archaeologist has managed to get his dreamwalking skill up to 39%. He decides to consume a coffee laced with one pinch of the black dust (enough to help, adding +1d4 to his skill, but not enough to bring on hallucinations or addiction) and rolls a 3, bringing his skill up to 42% for the duration of this dreamwalk. Because he is visiting a place he has been to before, his chance of successfully initiating the dreamwalk is +20%, or 62%, a roll which he makes. ",
				"His skill level for the duration of this dreamwalk is still considered to be 42%, and the player consults the table below to see what that means. Howard wants to visit a temple in the dreamlands that he has been to before, which, in relation to the real world, is roughly 700 miles away. The table tells him he has a range of 1000 miles, so this is fine. He also gains +2d6 points for his dreamself statistics on this particular dreamwalk. He rolls 9, and decides that he will put 5 of these points on his Magic Points, as he knows he is likely to have to cast spells, 2 on his Strength, because he intends to open a heavy stone door while in the dreamlands, and 2 points on his Appearance because he enjoys the frisson of being better looking in the dreamlands.",
				"Howard appears at the temple, and successfully opens the stone door in a STR vs STR roll, but then is horrified to discover one of the vast Spawn of Cthulhu within. Losing sanity, but not enough to drive him insane, Howard recognises that his foe is far too powerful to tackle alone and he tries to wake himself up. His chance of waking himself is 42% +40%, or 82%. The player rolls 91%! Unable to immediately return to the real world, Howard flees the temple, but is quickly caught in the clutches of the Spawn which then uses magic to search Howard’s mind and learn where he lives in the real world. Despite his extra Magic Points, the dice are cruel and Howard is unable to resist the MP vs MP mind read. His thoughts interrogated, the Spawn crushes Howard in its great claw, ‘killing’ his dreamself. Howard successfully rolls under his Intelligence x5 and awakes in the real world, shaken and sweating. He then considers moving house."
			]
			
		};
	