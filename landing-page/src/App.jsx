import { motion } from 'framer-motion'
import { MessageCircle, Zap, Shield, Smartphone, Globe, ArrowRight, Check } from 'lucide-react'
import ThreeScene from './components/ThreeScene'

const FeatureCard = ({ icon: Icon, title, description }) => (
  <motion.div 
    whileHover={{ y: -10, scale: 1.02 }}
    initial={{ opacity: 0, y: 20 }}
    whileInView={{ opacity: 1, y: 0 }}
    viewport={{ once: true }}
    className="glass p-8 rounded-3xl group cursor-default"
  >
    <div className="w-14 h-14 rounded-2xl bg-indigo-500/10 text-indigo-400 flex items-center justify-center mb-6 group-hover:bg-indigo-500/20 group-hover:text-indigo-300 transition-colors">
      <Icon size={28} />
    </div>
    <h3 className="font-display text-2xl font-bold mb-3">{title}</h3>
    <p className="text-zinc-400 leading-relaxed">{description}</p>
  </motion.div>
)

const Step = ({ number, title, description, isLast }) => (
  <div className="relative flex flex-col items-center text-center max-w-xs">
    <div className="w-16 h-16 rounded-full bg-zinc-900 border border-zinc-800 flex items-center justify-center text-2xl font-bold text-white mb-6 relative z-10">
      {number}
      <div className="absolute inset-0 rounded-full bg-indigo-500/20 blur-xl opacity-0 group-hover:opacity-100 transition-opacity"></div>
    </div>
    <h4 className="font-display text-xl font-bold mb-2">{title}</h4>
    <p className="text-zinc-500 text-sm leading-relaxed">{description}</p>
    {!isLast && (
      <div className="hidden md:block absolute top-8 left-full w-full h-[1px] bg-gradient-to-r from-indigo-500/50 to-transparent translate-x-4"></div>
    )}
  </div>
)

export default function App() {
  return (
    <div className="relative min-h-screen">
      <ThreeScene />

      {/* Navigation */}
      <nav className="fixed top-0 left-0 right-0 z-50 flex justify-center py-6">
        <div className="glass px-8 py-3 rounded-full flex items-center gap-12 text-sm font-medium">
          <div className="flex items-center gap-2">
            <div className="w-6 h-6 bg-indigo-500 rounded-lg flex items-center justify-center">
              <span className="text-white font-bold text-xs">C</span>
            </div>
            <span className="font-display font-bold text-lg tracking-tight">Converso</span>
          </div>
          <div className="hidden md:flex items-center gap-8 text-zinc-400">
            <a href="#features" className="hover:text-white transition-colors">Features</a>
            <a href="#how-it-works" className="hover:text-white transition-colors">How it Works</a>
          </div>
          <button className="bg-white text-black px-5 py-2 rounded-full font-bold hover:bg-zinc-200 transition-colors">
            Get Started
          </button>
        </div>
      </nav>

      <main>
        {/* Hero Section */}
        <section className="relative pt-48 pb-32 px-6">
          <div className="max-w-7xl mx-auto flex flex-col md:flex-row items-center gap-16">
            <motion.div 
              initial={{ opacity: 0, x: -50 }}
              animate={{ opacity: 1, x: 0 }}
              transition={{ duration: 0.8 }}
              className="flex-1 text-center md:text-left"
            >
              <div className="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-indigo-500/10 border border-indigo-500/20 mb-8">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span class="text-xs font-bold text-indigo-400 uppercase tracking-widest">Optimized for WordPress</span>
              </div>
              <h1 className="font-display text-5xl md:text-8xl font-black leading-tight mb-8">
                The Smart <br/>
                <span className="bg-gradient-to-r from-emerald-400 to-indigo-500 bg-clip-text text-transparent">WhatsApp</span> Button
              </h1>
              <p className="text-xl text-zinc-400 mb-12 max-w-xl leading-relaxed">
                Route visitors to the right agent instantly. Lightweight. Fast. Zero clutter. The final solution for WhatsApp integration on WordPress.
              </p>
              <div className="flex flex-col sm:flex-row gap-4 justify-center md:justify-start">
                <button className="px-10 py-5 bg-indigo-600 text-white rounded-2xl font-bold text-xl hover:bg-indigo-700 transition-all hover:shadow-2xl hover:shadow-indigo-500/40">
                  Get Started
                </button>
                <button className="px-10 py-5 bg-white/5 border border-white/10 text-white rounded-2xl font-bold text-xl hover:bg-white/10 transition-all">
                  View Demo
                </button>
              </div>
            </motion.div>

            <motion.div 
              initial={{ opacity: 0, scale: 0.8, rotate: -10 }}
              animate={{ opacity: 1, scale: 1, rotate: 0 }}
              transition={{ duration: 1, type: "spring" }}
              className="flex-1 relative"
            >
              <img 
                src="/whatsapp.png" 
                alt="3D WhatsApp Button" 
                className="w-full h-auto drop-shadow-[0_0_50px_rgba(16,185,129,0.3)]"
              />
              <div className="absolute -inset-10 bg-indigo-500/10 blur-[100px] -z-10 rounded-full"></div>
            </motion.div>
          </div>
        </section>

        {/* Features Section */}
        <section id="features" className="py-32 px-6">
          <div className="max-w-7xl mx-auto">
            <div className="mb-20 text-center">
              <h2 className="font-display text-4xl md:text-6xl font-bold mb-6">Designed for Performance</h2>
              <p className="text-zinc-500 text-lg">No heavy scripts, no bulky UI. Just pure speed.</p>
            </div>
            
            <div className="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
              <FeatureCard 
                icon={Globe}
                title="Multi-Agent Routing" 
                description="Intelligently distribute chats among multiple agents or departments based on availability."
              />
              <FeatureCard 
                icon={Zap}
                title="Zero Performance Impact" 
                description="Lightweight code that doesn't slow down your site. No heavy JS bundles to load."
              />
              <FeatureCard 
                icon={Shield}
                title="Privacy & Analytics" 
                description="Track redirection stats without compromising user privacy. GDPRO-ready by design."
              />
              <FeatureCard 
                icon={Smartphone}
                title="Mobile Optimized" 
                description="Fluid experience across all devices with native WhatsApp app detection."
              />
              <FeatureCard 
                icon={MessageCircle}
                title="Page-Level Control" 
                description="Choose exactly where the button appears: specific posts, pages, or categories."
              />
              <FeatureCard 
                icon={Check}
                title="Instant Setup" 
                description="Go live in under 60 seconds. Simple configuration, powerful results."
              />
            </div>
          </div>
        </section>

        {/* Dashboard Preview */}
        <section className="py-32 px-6 relative overflow-hidden">
          <div className="max-w-7xl mx-auto">
            <div className="glass rounded-[3rem] p-12 md:p-24 relative overflow-hidden">
                <div className="absolute -top-24 -right-24 w-96 h-96 bg-indigo-500/20 blur-[120px] rounded-full"></div>
                
                <div className="grid md:grid-cols-2 gap-20 items-center">
                    <div>
                        <h2 className="font-display text-4xl md:text-5xl font-bold mb-8 leading-tight text-glow">
                          Full Control Over <span className="text-indigo-400">Redirections</span>
                        </h2>
                        <ul className="space-y-6 mb-12">
                            {[
                                "Manage unlimited agents",
                                "Custom schedules & holidays",
                                "Deep linking to specific messages",
                                "Personalized greetings"
                            ].map((item, i) => (
                                <li key={i} className="flex items-center gap-4 text-zinc-400">
                                    <div className="w-6 h-6 rounded-full bg-emerald-500/20 border border-emerald-500/30 flex items-center justify-center text-emerald-500">
                                        <Check size={14} />
                                    </div>
                                    <span className="text-lg font-medium">{item}</span>
                                </li>
                            ))}
                        </ul>
                        <button className="flex items-center gap-2 text-indigo-400 font-bold hover:text-indigo-300 transition-colors group">
                          Learn about analytics <ArrowRight className="group-hover:translate-x-1 transition-transform" />
                        </button>
                    </div>
                    <motion.div 
                        initial={{ rotateY: 20, rotateX: 10 }}
                        whileInView={{ rotateY: 0, rotateX: 0 }}
                        transition={{ duration: 1.5 }}
                        className="relative"
                    >
                        <img src="/dashboard.png" alt="Converso Dashboard" className="rounded-2xl border border-white/10 shadow-2xl" />
                    </motion.div>
                </div>
            </div>
          </div>
        </section>

        {/* How it Works */}
        <section id="how-it-works" className="py-32 px-6">
          <div className="max-w-5xl mx-auto">
            <h2 className="font-display text-4xl font-bold mb-20 text-center">3 Steps to Better Conversions</h2>
            <div className="grid md:grid-cols-3 gap-12 relative">
                <Step 
                    number="01" 
                    title="Add Agents" 
                    description="Enter WhatsApp numbers and names for your team members." 
                />
                <Step 
                    number="02" 
                    title="Customize Button" 
                    description="Choose your styling, position, and page visibility settings." 
                />
                <Step 
                    number="03" 
                    title="Engage Fast" 
                    isLast 
                    description="Visitors click and are instantly routed to an available agent." 
                />
            </div>
          </div>
        </section>

        {/* Performance Emphasis */}
        <section className="py-32 bg-zinc-900/50 border-y border-white/5">
            <div className="max-w-7xl mx-auto px-6 grid md:grid-cols-3 gap-12 text-center">
                <div>
                    <div className="text-5xl font-black font-display text-indigo-500 mb-2">0ms</div>
                    <p className="text-zinc-500 uppercase tracking-widest font-bold text-sm">UI Loading Delay</p>
                </div>
                <div>
                    <div className="text-5xl font-black font-display text-emerald-500 mb-2">&lt; 10KB</div>
                    <p className="text-zinc-500 uppercase tracking-widest font-bold text-sm">Script Size</p>
                </div>
                <div>
                    <div className="text-5xl font-black font-display text-violet-500 mb-2">100/100</div>
                    <p className="text-zinc-500 uppercase tracking-widest font-bold text-sm">Lighthouse Score</p>
                </div>
            </div>
        </section>

        {/* Final CTA */}
        <section className="py-48 px-6 text-center relative overflow-hidden">
          <div className="max-w-4xl mx-auto relative z-10">
            <h2 className="font-display text-6xl md:text-8xl font-black mb-12">Stop Losing <br/> Chat Leads.</h2>
            <button className="group relative px-12 py-6 bg-white text-black rounded-2xl font-black text-2xl hover:bg-zinc-200 transition-all hover:scale-105 active:scale-95">
               Download Converso
               <div className="absolute -inset-4 bg-white/20 blur-2xl rounded-2xl -z-10 group-hover:bg-white/40 transition-colors"></div>
            </button>
            <p className="mt-12 text-zinc-500 font-medium tracking-tight">Requires WordPress 6.0+ • Free to use • No account needed</p>
          </div>
          
          <div className="absolute bottom-0 left-1/2 -translate-x-1/2 w-[800px] h-[400px] bg-indigo-500/10 blur-[150px] rounded-full -z-10"></div>
        </section>
      </main>

      <footer className="py-12 border-t border-white/5 px-6">
        <div className="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-8 text-zinc-500 text-sm">
          <div className="flex items-center gap-2">
            <div className="w-6 h-6 bg-zinc-800 rounded flex items-center justify-center">
              <span className="text-white font-bold text-xs">C</span>
            </div>
            <span className="font-display font-bold text-white text-lg tracking-tight">Converso</span>
          </div>
          <div className="flex gap-12">
            <a href="#" className="hover:text-white transition-colors">Documentation</a>
            <a href="#" className="hover:text-white transition-colors">Support</a>
            <a href="#" className="hover:text-white transition-colors">GitHub</a>
          </div>
          <div>© 2026 Converso. Crafted for performance.</div>
        </div>
      </footer>
    </div>
  )
}
