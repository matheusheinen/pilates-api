<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
const COLORS = {bgDark: '#121212',elementDark: '#0F1616',accent: '#0f766e',text: 'white'};

const navItems = [
  { name: 'O Estúdio', href: '#sobre' },
  { name: 'A Jornada', href: '#jornada' },
  { name: 'Princípios', href: '#principios' }, 
  { name: 'Benefícios', href: '#beneficios' }, 
  { name: 'Galeria', href: '#galeria' },
  { name: 'Localização', href: '#localizacao' },
];

const useOnScreen = (targetRef, rootMargin = '0px') => {
  const isVisible = ref(false);
  let observer;

  onMounted(() => {
    if (!('IntersectionObserver' in window)) return;
    observer = new IntersectionObserver(
      ([entry]) => {if (entry.isIntersecting && !isVisible.value) {isVisible.value = true;}},
      {rootMargin, threshold: 0.1}
    );

    if (targetRef.value) {observer.observe(targetRef.value);}
  });

  onUnmounted(() => {
    if (observer && targetRef.value) {observer.unobserve(targetRef.value);}
    if (observer) {observer.disconnect();}
  });
  return isVisible;
};

const scrollPosition = ref(0);
const handleScroll = () => {scrollPosition.value = window.pageYOffset;};

onMounted(() => {window.addEventListener('scroll', handleScroll, { passive: true });handleScroll(); });
onUnmounted(() => {window.removeEventListener('scroll', handleScroll);});
const heroOpacity = computed(() => {return 1 - Math.min(1, scrollPosition.value / 400);});
const heroScale = computed(() => {return 1 - Math.min(0.3, scrollPosition.value / 1500);});
const heroTransform = computed(() => {const offset = scrollPosition.value * 0.1;return `scale(${heroScale.value}) translateY(-${offset}px)`;});
const parallaxOffset = computed(() => {return scrollPosition.value * 0.3;});


const sobreRef = ref(null);
const jornadaRef = ref(null);
const principiosRef = ref(null);
const beneficiosRef = ref(null);
const galeriaRef = ref(null);
const localizacaoRef = ref(null);

const isSobreVisible = useOnScreen(sobreRef);
const isJornadaVisible = useOnScreen(jornadaRef);
const isPrincipiosVisible = useOnScreen(principiosRef);
const isBeneficiosVisible = useOnScreen(beneficiosRef);
const isGaleriaVisible = useOnScreen(galeriaRef); 
const isLocalizacaoVisible = useOnScreen(localizacaoRef); 
const getRevealClasses = (isVisible) => {return {'opacity-100 translate-y-0': isVisible,'opacity-0 translate-y-[50px]': !isVisible,'transition-all duration-1000 ease-out': true};};

const ElementCard = {
  props: ['title', 'content', 'icon', 'colors'],
  setup(props) {
    const isHovered = ref(false);
    const cardStyle = computed(() => ({backgroundColor: `${props.colors.elementDark}c0`,color: props.colors.text,border: isHovered.value ? `2px solid ${props.colors.accent}` : '2px solid transparent',}));

    return { isHovered, cardStyle, colors: props.colors };
  },
  template: `
    <div class="p-6 rounded-2xl shadow-2xl transition-all duration-300 ease-in-out cursor-pointer hover:shadow-2xl hover:scale-[1.02] transform backdrop-blur-sm" :style="cardStyle" @mouseenter="isHovered = true" @mouseleave="isHovered = false">
      <div class="flex items-center space-x-4 mb-4">
        <!-- SVG Icon Path -->
        <svg class="w-8 h-8 flex-shrink-0" :style="{ color: colors.accent }" fill="currentColor" viewBox="0 0 24 24"><path :d="icon" /></svg>
        <h3 class="text-xl font-semibold tracking-wide" :style="{ color: colors.accent }">{{ title }}</h3>
      </div>
      <p class="text-gray-300">{{ content }}</p>
    </div>
  `
};
const principlesData = [
    { title: "Respiração", content: "O elo entre corpo e mente. A respiração correta é essencial para a estabilização do tronco e a fluidez do movimento.", icon: "M10 20h4v2h-4zM20 10h2v4h-2zM4 10h2v4H4zM12 2c-4.41 0-8 3.59-8 8s3.59 8 8 8 8-3.59 8-8-3.59-8-8-8zm0 14c-3.31 0-6-2.69-6-6s2.69-6 6-6 6 2.69 6 6-2.69 6-6 6zM12 6l-2 2h4l-2-2z" },
    { title: "Concentração", content: "Cada exercício é feito com total atenção à forma e à sensação, garantindo o máximo controle muscular.", icon: "M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z" },
    { title: "Controle", content: "Não há movimentos desleixados. O controle total sobre o corpo é a base para a execução segura e eficaz.", icon: "M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V5h2v4z" },
    { title: "Precisão", content: "A qualidade do movimento supera a quantidade. Os exercícios são realizados com atenção aos detalhes para o alinhamento ideal.", icon: "M20 6h-4V4h4v2zm-6 0h-4V4h4v2zm-6 0H4V4h4v2zM20 12h-4v-2h4v2zm-6 0h-4v-2h4v2zm-6 0H4v-2h4v2zM20 18h-4v-2h4v2zm-6 0h-4v-2h4v2zm-6 0H4v-2h4v2z" },
    { title: "Centramento", content: "Todos os movimentos emanam do 'Powerhouse' (abdômen, lombar e glúteos), o centro de força do corpo.", icon: "M12 2a10 10 0 00-7.07 2.93L12 12l7.07-7.07A10 10 0 0012 2zm0 18a8 8 0 110-16 8 8 0 010 16z" },
    { title: "Fluidez", content: "Movimentos graciosos e contínuos que conectam os exercícios, como uma dança entre força e alongamento.", icon: "M13 3h-2v2h2V3zm0 4h-2v2h2V7zm0 4h-2v2h2v-2zm0 4h-2v2h2v-2zm-6-4h2v2H7v-2zm-2 4h2v2H5v-2zm-2-4h2v2H3v-2zm0 4h2v2H3v-2zm8 4h2v2h-2v-2zm-2-4h2v2h-2v-2zm0 8h2v2h-2v-2zm-4-4h2v2H7v-2zm0 4h2v2H7v-2zm8-4h2v2h-2v-2zm0 4h2v2h-2v-2zm2-8h2v2h-2v-2z" },
];
</script>

<template>
  <div class="min-h-screen relative overflow-x-hidden font-sans" :style="{ backgroundColor: COLORS.bgDark, color: COLORS.text }">
    <div class="fixed inset-0 w-full h-[200vh] z-0" :style="{ backgroundColor: COLORS.elementDark, transform: `translateY(-${parallaxOffset}px)`, backgroundImage: `radial-gradient(circle at 50% 100%, ${COLORS.accent}15 10%, transparent 60%), radial-gradient(circle at 10% 20%, ${COLORS.accent}0a 5%, transparent 30%), linear-gradient(180deg, ${COLORS.bgDark} 0%, ${COLORS.elementDark} 100%)` }"></div>
    <header class="sticky top-0 z-50 backdrop-blur-md transition-all duration-300">
      <div class="flex justify-between items-center py-4 px-6 md:px-12 max-w-7xl mx-auto">
        <a href="#home" class="flex items-center space-x-2 text-2xl md:text-3xl font-extrabold tracking-wider">
          <img src = "../public/logo.png" width="150px">
        </a>
        <nav class="hidden md:flex space-x-8">
          <a v-for="(item, i) in navItems" :key="i" :href="item.href" class="text-white relative group text-lg font-medium opacity-90 hover:opacity-100 transition-all">
            {{ item.name }}
            <span class="absolute bottom-0 left-0 w-full h-0.5 scale-x-0 group-hover:scale-x-100 transition-all duration-300" :style="{ backgroundColor: COLORS.accent }"></span>
          </a>
        </nav>
      </div>
    </header>
    <section id="home" class="relative h-[80vh] md:h-[90vh] flex flex-col items-center justify-center overflow-hidden z-20">
        <div class="relative z-20 text-center p-4 md:p-8 transition-all duration-0" :style="{ opacity: heroOpacity, transform: heroTransform }">
        <h1 class="text-5xl md:text-8xl font-black mb-4 tracking-tighter" :style="{ color: COLORS.accent }">POSTURA & EQUILÍBRIO</h1>
        <h2 class="text-xl md:text-3xl max-w-4xl font-light leading-relaxed italic text-gray-300">Proporcionar uma jornada de <span class="font-semibold" :style="{ color: COLORS.accent }">reconexão com a natureza</span>, explorando cuidadosamente o seu interior.</h2>
        <a href="/login" class="mt-12 inline-block px-10 py-4 rounded-full text-lg font-bold shadow-xl transition-all duration-300 hover:scale-105 uppercase tracking-wider" :style="{ backgroundColor: COLORS.accent, color: COLORS.elementDark }">Saiba Mais</a>
      </div>
    </section>
    <main class="relative z-30 pt-16 md:pt-24 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <section id="sobre" ref="sobreRef" class="py-20 md:py-32" :class="getRevealClasses(isSobreVisible)">
        <h2 class="text-4xl md:text-6xl font-extrabold mb-16 text-center" :style="{ color: COLORS.accent }">Tayla Portaluppi: A Instrutora</h2>

        <div class="flex flex-col md:flex-row items-center md:space-x-16">
          <div class="w-full md:w-1/3 mb-8 md:mb-0 rounded-2xl overflow-hidden shadow-2xl aspect-square  transform hover:scale-[1.01] transition-transform duration-500 flex items-center justify-center">
            <img src="../public/professora.png" alt="Foto da Instrutora Tayla Portaluppi" class="w-full h-full object-cover opacity-70">
          </div>

          <div class="w-full md:w-2/3 text-xl space-y-6 text-gray-200 border-l-4 pl-8" :style="{ borderColor: COLORS.accent }">
            <p>Formada em Educação Física pela Univates em Agosto de 2022, a jornada de Tayla no Pilates começou muito antes, impulsionada por um sonho de criar um ambiente único de cuidado, onde cada detalhe é pensado para o seu bem-estar.</p>
            <p class="italic font-semibold text-2xl" :style="{ color: COLORS.accent }">"O Pilates é a arte de alinhar corpo, mente e respiração. Meu propósito é guiar cada pessoa para um estado de equilíbrio profundo, onde o movimento deixa de ser obrigação e vira liberdade, promovendo saúde de forma integral."</p>
            <p>A instrutora acredita que cada aluno possui uma história, um ritmo e um limite — e que o verdadeiro resultado vem quando o corpo se expressa sem pressões, numa jornada acolhedora e transformadora. Com turmas reduzidas, ela garante atenção individualizada e correção precisa em cada exercício.</p>
            <p>Seu estúdio nasce desse ideal: um espaço pensado para inspirar evolução, cuidado e leveza, onde cada detalhe existe para expandir o bem‑estar e alcançar seus objetivos de saúde e condicionamento físico.</p>
          </div>
        </div>
      </section>
      <section id="jornada" ref="jornadaRef" class="py-20 md:py-32" :class="getRevealClasses(isJornadaVisible)">
        <h2 class="text-4xl md:text-6xl font-extrabold mb-16 text-center" :style="{ color: COLORS.accent }">O Caminho para a Sua Melhor Versão</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-12">
          <ElementCard title="Consciência Corporal" content="Desenvolva uma percepção aprimorada do seu corpo, fundamental para a precisão dos movimentos e para evitar lesões no dia a dia." icon="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm0-13a1 1 0 100 2 1 1 0 000-2z" :colors="COLORS"/>
          <ElementCard title="Força & Estabilidade" content="Fortaleça seu Core (Powerhouse) e os músculos profundos, garantindo uma coluna mais saudável, postura correta e maior resistência." icon="M10 20h4v2h-4v-2zm-6-2h16v-2H4v2zm0-4h16v-2H4v2zm0-4h16V8H4v2zM6 6h12V4H6v2z":colors="COLORS"/>
          <ElementCard title="Equilíbrio Interior" content="Integre respiração e movimento para alcançar um estado de presença e calma, reduzindo o stress e promovendo a clareza mental." icon="M12 3l-8 4v7c0 3.86 3.14 7 7 7s7-3.14 7-7V7l-8-4zm0 15c-2.76 0-5-2.24-5-5V9l5-2.5 5 2.5v3c0 2.76-2.24 5-5 5z":colors="COLORS" />
        </div>
      </section>
      <section id="principios" ref="principiosRef" class="py-20 md:py-32" :class="getRevealClasses(isPrincipiosVisible)">
        <h2 class="text-4xl md:text-6xl font-extrabold mb-16 text-center" :style="{ color: COLORS.accent }">Os 6 Princípios Fundamentais</h2>
        <p class="text-center text-lg max-w-4xl mx-auto mb-16 text-gray-300">O Método Pilates é regido por pilares que garantem a execução correta e a máxima eficácia de cada exercício. Conheça a base da nossa filosofia de movimento.</p>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
          <ElementCard v-for="(p, i) in principlesData" :key="i" :title="p.title" :content="p.content" :icon="p.icon" :colors="COLORS"/>
        </div>
      </section>
      <section id="beneficios" ref="beneficiosRef" class="py-20 md:py-32 bg-opacity-10" :class="getRevealClasses(isBeneficiosVisible)" :style="{ backgroundColor: `${COLORS.elementDark}50` }">
        <h2 class="text-4xl md:text-6xl font-extrabold mb-16 text-center" :style="{ color: COLORS.accent }">Pilates: Para Quem e Por Quê?</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-16">
          <div class="space-y-8">
            <h3 class="text-3xl font-bold border-b-2 pb-2" :style="{ borderColor: COLORS.accent, color: COLORS.accent }">Benefícios para o Corpo</h3>
            <ul class="space-y-4 text-lg text-gray-200 list-none pl-0">
              <li class="flex items-start">
                <svg class="w-6 h-6 mr-3 mt-1 flex-shrink-0" :style="{ color: COLORS.accent }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944c-1.325.688-2.618 1.57-3.842 2.651M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p><strong>Alívio de Dores Crônicas:</strong> Fortalecimento muscular que suporta a coluna e articulações, diminuindo a pressão em pontos doloridos.</p>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 mr-3 mt-1 flex-shrink-0" :style="{ color: COLORS.accent }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944c-1.325.688-2.618 1.57-3.842 2.651M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p><strong>Melhora da Postura:</strong> Alinhamento e fortalecimento do centro (Core) que corrige desequilíbrios posturais.</p>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 mr-3 mt-1 flex-shrink-0" :style="{ color: COLORS.accent }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944c-1.325.688-2.618 1.57-3.842 2.651M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p><strong>Aumento de Flexibilidade:</strong> Maior amplitude de movimento sem comprometer a estabilidade articular.</p>
              </li>
              <li class="flex items-start">
                <svg class="w-6 h-6 mr-3 mt-1 flex-shrink-0" :style="{ color: COLORS.accent }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944c-1.325.688-2.618 1.57-3.842 2.651M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p><strong>Recuperação de Lesões:</strong> Fortalece músculos sem impacto, sendo ideal para reabilitação e prevenção de novas lesões.</p>
              </li>
            </ul>
          </div>
          <div class="space-y-8">
            <h3 class="text-3xl font-bold border-b-2 pb-2" :style="{ borderColor: COLORS.accent, color: COLORS.accent }">Grupos que mais se Beneficiam</h3>
            <ul class="space-y-6 text-lg text-gray-200 list-none pl-0">
              <li class="p-4 rounded-lg" :style="{ backgroundColor: COLORS.elementDark }">
                <strong class="text-xl block mb-1" :style="{ color: COLORS.accent }">Gestantes:</strong> Ajuda na estabilidade pélvica, alívio de dores nas costas e preparação muscular para o parto.
              </li>
              <li class="p-4 rounded-lg" :style="{ backgroundColor: COLORS.elementDark }">
                <strong class="text-xl block mb-1" :style="{ color: COLORS.accent }">Idosos:</strong> Melhora do equilíbrio, prevenção de quedas, aumento da força e manutenção da densidade óssea.
              </li>
              <li class="p-4 rounded-lg" :style="{ backgroundColor: COLORS.elementDark }">
                <strong class="text-xl block mb-1" :style="{ color: COLORS.accent }">Atletas:</strong> Potencializa a performance, aumentando a flexibilidade, a capacidade respiratória e prevenindo o overtraining por desequilíbrios musculares.
              </li>
              <li class="p-4 rounded-lg" :style="{ backgroundColor: COLORS.elementDark }">
                <strong class="text-xl block mb-1" :style="{ color: COLORS.accent }">Pessoas em Reabilitação:</strong> Método de baixo impacto, perfeito para o retorno gradual ao movimento após cirurgias ou lesões.
              </li>
            </ul>
          </div>
        </div>
      </section>

      <section id="galeria" ref="galeriaRef" class="py-20 md:py-32" :class="getRevealClasses(isGaleriaVisible)">
        <h2 class="text-4xl md:text-6xl font-extrabold mb-16 text-center" :style="{ color: COLORS.accent }">Ambiente Moderno</h2>
        <p class="text-center text-lg max-w-3xl mx-auto mb-12 text-gray-300">Conheça o ambiente acolhedor e moderno do estúdio, projetado para o seu conforto e concentração durante as aulas.</p>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
          <div v-for="(src, i) in ['https://sistema.bendfy.com/1.jpg','https://sistema.bendfy.com/2.jpg','https://sistema.bendfy.com/3.jpg','https://sistema.bendfy.com/4.jpg']" :key="i" class="rounded-xl overflow-hidden border-2" :style="{ borderColor: COLORS.accent }">
            <div class="w-full aspect-[4/3] overflow-hidden">
              <img :src="src" :alt="`Galeria ${i+1}`" class="w-full h-full object-cover transform transition-transform duration-500 hover:scale-105">
            </div>
          </div>
        </div>
      </section>
      <section id="localizacao" ref="localizacaoRef" class="py-20 md:py-32">
        <h2 class="text-4xl md:text-6xl font-extrabold mb-16 text-center" :style="{ color: COLORS.accent }">Localização</h2>
        <p class="text-center text-lg max-w-3xl mx-auto mb-12 text-gray-300">Estamos localizados em um ponto de fácil acesso, com um ambiente tranquilo e inspirador para suas sessões de Pilates. Venha nos visitar!</p>

        <div class="max-w-4xl mx-auto rounded-xl overflow-hidden shadow-2xl border-4" :style="{ borderColor: COLORS.accent, backgroundColor: COLORS.elementDark }">
          <iframe src="https://www.google.com/maps/embed?pb=!4v1765181746294!6m8!1m7!1sBQ8yrh1kxrK0lfbrEuzD_Q!2m2!1d-28.84431411187509!2d-51.8901865920318!3f83.67066166787077!4f-4.318799030573601!5f0.7820865974627469"width="100%"height="450"style="border:0;"allowfullscreen=""loading="lazy"referrerpolicy="no-referrer-when-downgrade"title="Localização do Estúdio de Pilates"></iframe>
        </div>
        
        <div class="text-center mt-8 text-lg font-medium text-gray-300">
          <p>Av. Alberto Pasqualini, 519, Centro, G7 Edifício Los Angeles, sala 04 - Guaporé/RS</p>
          <a href="/login" class="text-green-400 hover:underline transition-colors mt-2 inline-block">Agende uma aula</a>
        </div>
      </section>
    </main>

    <footer class="py-8 text-center text-sm text-gray-400 border-t mt-24" :style="{ borderColor: `${COLORS.elementDark}80` }">© {{ new Date().getFullYear() }} Pilates Tayla Portaluppi. Todos os direitos reservados.</footer>
  </div>
</template>