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
            
            // Add new prompt line securely
            const newPrompt = document.createElement('div');
            newPrompt.className = "flex gap-2 pt-2";
            
            const spanArrow = document.createElement('span');
            spanArrow.className = "text-lab-terminal";
            spanArrow.textContent = "➜";
            
            const spanTilde = document.createElement('span');
            spanTilde.className = "text-lab-cyan";
            spanTilde.textContent = "~";
            
            const spanCursor = document.createElement('span');
            spanCursor.className = "animate-blink inline-block w-2 h-4 align-middle bg-lab-primary";
            
            newPrompt.append(spanArrow, spanTilde, spanCursor);
            this.containerTarget.appendChild(newPrompt);
            
            // Hide original cursor
            if (this.hasCursorTarget) {
                this.cursorTarget.style.display = 'none';
            }
        }, 500);
    }
}
