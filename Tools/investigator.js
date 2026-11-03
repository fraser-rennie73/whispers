class Investigator {
	
	static get toolbox() {
		return {
			title: 'Investigator',
			icon: '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/></svg>'
		};
	}
	
	static get conversionConfig() {
		return {
			export: 'text', // this property of tool data will be used as string to pass to other tool
			import: 'text' // to this property imported string will be passed
		};
	}

	constructor({data}){
		this.data = data;
	}
	
	render(){

		const inputBox = (prop, title) => {
			
			title = title || prop;

			var elem = document.createElement('div');
			elem.classList.add('cdx-input');
			elem.contentEditable = true;
			elem.innerHTML = this.data && this.data[prop] ? this.data[prop] : '';
			elem.dataset.placeholder = title;
						
			return elem;
		};
		
		const wrapper = document.createElement('div');
		wrapper.classList.add('cdx-block');

		var nameControl = inputBox("text", "Name");
		var descControl = inputBox("description", "Description");
		var imgControl = inputBox("imageUrl", "Image");
		var linkControl = inputBox("linkUrl", "Link");
		var playerControl = inputBox("player", "Player");

		wrapper.appendChild(nameControl);
		wrapper.appendChild(descControl);
		wrapper.appendChild(imgControl);
		wrapper.appendChild(linkControl);
		wrapper.appendChild(playerControl);
		
		this.inputControl = nameControl;		
				
		return wrapper;
	}

	save(blockContent){
		return {
			text: blockContent.children[0].innerHTML || null,
			description: blockContent.children[1].innerHTML || null,
			imageUrl: blockContent.children[2].innerHTML || null,
			linkUrl: blockContent.children[3].innerHTML || null,
			player: blockContent.children[4].innerHTML || null
		}
	}

	/*
	validate(savedData){
		if (!savedData.text.trim() || !savedData.value.trim()){
			return false;
		}

		return true;
	}
	*/
}