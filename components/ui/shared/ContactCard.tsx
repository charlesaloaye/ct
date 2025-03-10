
const ContactCard = ({cta, description, icon}) => {
  return (
    <li>
    <div className="icon">
    {icon}
    </div>
    <div className="text">
        <label>{cta}</label>
        <p>{description}</p>
    </div>
</li>
  )
}

export default ContactCard
