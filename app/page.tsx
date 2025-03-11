// import Menu from "@/components/Menu";
import About from "@/components/ui/About";
import Contact from "@/components/ui/Contact";
import Hero from "@/components/ui/Hero";
import Portfolio from "@/components/ui/Portfolio";
import Services from "@/components/ui/Services";
// import Testimonies from "@/components/ui/Testimonies";

export default function Home() {
  return (
  <>
  <Hero/>
  <About/>
  <Services/>
  <Portfolio/>
  {/* <Testimonies/> */}
  <Contact/>
  </>
  );
}
