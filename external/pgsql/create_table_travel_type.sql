-- Table: public.travel_type

-- DROP TABLE public.travel_type;

CREATE TABLE public.travel_type
(
  id serial NOT NULL,
  name character varying(16),
  is_provider boolean NOT NULL DEFAULT false,
  CONSTRAINT travel_type_pkey PRIMARY KEY (id)
)
WITH (
OIDS=FALSE
);
ALTER TABLE public.travel_type
OWNER TO "o2-carpool";
