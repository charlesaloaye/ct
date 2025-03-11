interface ContactCardProps {
  cta: string;
  description: string;
  icon: React.ReactNode;
}

const ContactCard: React.FC<ContactCardProps> = ({ cta, description, icon }) => {
  return (
    <li>
      <div className="icon">{icon}</div>
      <div className="text">
        <label>{cta}</label>
        <p dangerouslySetInnerHTML={{__html: description}}/>
      </div>
    </li>
  );
};

export default ContactCard;
