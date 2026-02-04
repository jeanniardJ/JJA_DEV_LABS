import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ["text", "cursor", "container"];
    static values = {
        command: String,
        result: String,
        speed: { type: Number, default: 50 }
    }

    connect() {
        this.index = 0;
        this.typeChar();
    }

    typeChar() {
        if (this.index < this.commandValue.length) {
            this.textTarget.textContent += this.commandValue.charAt(this.index);
            this.index++;
            setTimeout(() => this.typeChar(), Math.random() * this.speedValue + this.speedValue);
        } else {
            this.showResult();
        }
    }

    showResult() {
        setTimeout(() => {
            const resultDiv = document.createElement('div');
            resultDiv.className = "text-lab-primary pl-4 mt-1 block leading-relaxed";
            resultDiv.textContent = this.resultValue;
            this.containerTarget.appendChild(resultDiv);
            
            // Add new prompt line
            const newPrompt = document.createElement('div');
            newPrompt.className = "flex gap-2 pt-2";
            newPrompt.innerHTML = '<span class="text-lab-terminal">➜</span><span class="text-lab-cyan">~</span><span class="animate-blink inline-block w-2 h-4 align-middle bg-lab-primary"></span>';
            this.containerTarget.appendChild(newPrompt);
            
            // Hide original cursor
            if (this.hasCursorTarget) {
                this.cursorTarget.style.display = 'none';
            }
        }, 500);
    }
}
