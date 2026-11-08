import PageShell from '../components/PageShell.jsx';
import ContactForm from '../components/ContactForm.jsx';

export default function Contact({ copy, contactCopy }) {
  return (
    <PageShell copy={copy}>
      <ContactForm copy={{ ...contactCopy, heading: null }} />
    </PageShell>
  );
}
